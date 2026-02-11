<?php

declare(strict_types=1);

namespace Pricing\Application\ImportDefaultPricing;

use Illuminate\Support\Facades\DB;
use Pricing\Infrastructure\Persistence\Models\DiscountRuleEloquentModel;
use Pricing\Infrastructure\Persistence\Models\DurationDefinitionEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Pricing\Infrastructure\Persistence\Models\PricingRateEloquentModel;
use Ramsey\Uuid\Uuid;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

/**
 * Handler pour importer une grille tarifaire d'un tenant source vers un tenant cible.
 */
final class ImportDefaultPricingHandler
{
    private const DEFAULT_TENANT_SLUG = 'default';

    public function handle(ImportDefaultPricingCommand $command): ImportDefaultPricingResponse
    {
        // Résoudre le tenant source (null = tenant par défaut)
        $sourceTenant = $command->sourceTenantId !== null
            ? TenantEloquentModel::find($command->sourceTenantId)
            : TenantEloquentModel::where('slug', self::DEFAULT_TENANT_SLUG)->first();

        if ($sourceTenant === null) {
            return new ImportDefaultPricingResponse(
                success: false,
                message: $command->sourceTenantId !== null
                    ? 'Source tenant not found'
                    : 'Default tenant not found',
                imported: [
                    'pricing_classes' => 0,
                    'durations' => 0,
                    'rates' => 0,
                    'discount_rules' => 0,
                ],
            );
        }

        $sourceTenantId = $sourceTenant->id;
        $targetTenantId = $command->targetTenantId;

        // Empêcher la copie vers le même tenant
        if ($sourceTenantId === $targetTenantId) {
            return new ImportDefaultPricingResponse(
                success: false,
                message: 'Source and target tenant cannot be the same',
                imported: [
                    'pricing_classes' => 0,
                    'durations' => 0,
                    'rates' => 0,
                    'discount_rules' => 0,
                ],
            );
        }

        $imported = [
            'pricing_classes' => 0,
            'durations' => 0,
            'rates' => 0,
            'discount_rules' => 0,
        ];

        // Maps pour conserver les correspondances ID source → ID nouveau
        $pricingClassMap = [];
        $durationMap = [];

        DB::transaction(function () use (
            $sourceTenantId,
            $targetTenantId,
            $command,
            &$imported,
            &$pricingClassMap,
            &$durationMap,
        ) {
            // 1. Import Pricing Classes
            if ($command->copyPricingClasses) {
                $sourceClasses = PricingClassEloquentModel::withoutGlobalScope('tenant')
                    ->where('tenant_id', $sourceTenantId)
                    ->where('is_active', true)
                    ->get();

                foreach ($sourceClasses as $sourceClass) {
                    $existing = PricingClassEloquentModel::withoutGlobalScope('tenant')
                        ->where('tenant_id', $targetTenantId)
                        ->where('code', $sourceClass->code)
                        ->first();

                    if ($existing !== null) {
                        $pricingClassMap[$sourceClass->id] = $existing->id;
                        continue;
                    }

                    $newId = Uuid::uuid4()->toString();
                    PricingClassEloquentModel::withoutGlobalScope('tenant')->create([
                        'id' => $newId,
                        'tenant_id' => $targetTenantId,
                        'code' => $sourceClass->code,
                        'label' => $sourceClass->label,
                        'description' => $sourceClass->description,
                        'color' => $sourceClass->color,
                        'sort_order' => $sourceClass->sort_order,
                        'is_active' => true,
                    ]);

                    $pricingClassMap[$sourceClass->id] = $newId;
                    $imported['pricing_classes']++;
                }
            } else {
                // Construire le mapping depuis les classes existantes
                $this->buildPricingClassMap($sourceTenantId, $targetTenantId, $pricingClassMap);
            }

            // 2. Import Durations
            if ($command->copyDurations) {
                $sourceDurations = DurationDefinitionEloquentModel::withoutGlobalScope('tenant')
                    ->where('tenant_id', $sourceTenantId)
                    ->where('is_active', true)
                    ->get();

                foreach ($sourceDurations as $sourceDuration) {
                    $existing = DurationDefinitionEloquentModel::withoutGlobalScope('tenant')
                        ->where('tenant_id', $targetTenantId)
                        ->where('code', $sourceDuration->code)
                        ->first();

                    if ($existing !== null) {
                        $durationMap[$sourceDuration->id] = $existing->id;
                        continue;
                    }

                    $newId = Uuid::uuid4()->toString();
                    DurationDefinitionEloquentModel::withoutGlobalScope('tenant')->create([
                        'id' => $newId,
                        'tenant_id' => $targetTenantId,
                        'code' => $sourceDuration->code,
                        'label' => $sourceDuration->label,
                        'duration_hours' => $sourceDuration->duration_hours,
                        'duration_days' => $sourceDuration->duration_days,
                        'is_custom' => $sourceDuration->is_custom,
                        'sort_order' => $sourceDuration->sort_order,
                        'is_active' => true,
                    ]);

                    $durationMap[$sourceDuration->id] = $newId;
                    $imported['durations']++;
                }
            } else {
                // Construire le mapping depuis les durées existantes
                $this->buildDurationMap($sourceTenantId, $targetTenantId, $durationMap);
            }

            // 3. Import Pricing Rates
            if ($command->copyRates) {
                $sourceRates = PricingRateEloquentModel::withoutGlobalScope('tenant')
                    ->where('tenant_id', $sourceTenantId)
                    ->where('is_active', true)
                    ->get();

                // Récupérer les catégories du tenant cible
                $targetCategories = DB::table('categories')
                    ->where('tenant_id', $targetTenantId)
                    ->pluck('id')
                    ->toArray();

                foreach ($sourceRates as $sourceRate) {
                    // Vérifier que la catégorie existe dans le tenant cible
                    if (!in_array($sourceRate->category_id, $targetCategories, true)) {
                        continue;
                    }

                    $newPricingClassId = $pricingClassMap[$sourceRate->pricing_class_id] ?? null;
                    $newDurationId = $durationMap[$sourceRate->duration_id] ?? null;

                    if ($newPricingClassId === null || $newDurationId === null) {
                        continue;
                    }

                    $existing = PricingRateEloquentModel::withoutGlobalScope('tenant')
                        ->where('tenant_id', $targetTenantId)
                        ->where('category_id', $sourceRate->category_id)
                        ->where('pricing_class_id', $newPricingClassId)
                        ->where('duration_id', $newDurationId)
                        ->first();

                    if ($existing !== null) {
                        continue;
                    }

                    PricingRateEloquentModel::withoutGlobalScope('tenant')->create([
                        'id' => Uuid::uuid4()->toString(),
                        'tenant_id' => $targetTenantId,
                        'category_id' => $sourceRate->category_id,
                        'pricing_class_id' => $newPricingClassId,
                        'duration_id' => $newDurationId,
                        'price' => $sourceRate->price,
                        'is_active' => true,
                    ]);

                    $imported['rates']++;
                }
            }

            // 4. Import Discount Rules
            if ($command->copyDiscountRules) {
                $sourceDiscounts = DiscountRuleEloquentModel::withoutGlobalScope('tenant')
                    ->where('tenant_id', $sourceTenantId)
                    ->where('is_active', true)
                    ->get();

                foreach ($sourceDiscounts as $sourceDiscount) {
                    $newPricingClassId = $sourceDiscount->pricing_class_id !== null
                        ? ($pricingClassMap[$sourceDiscount->pricing_class_id] ?? null)
                        : null;

                    $newDurationId = $sourceDiscount->min_duration_id !== null
                        ? ($durationMap[$sourceDiscount->min_duration_id] ?? null)
                        : null;

                    $existing = DiscountRuleEloquentModel::withoutGlobalScope('tenant')
                        ->where('tenant_id', $targetTenantId)
                        ->where('label', $sourceDiscount->label)
                        ->first();

                    if ($existing !== null) {
                        continue;
                    }

                    DiscountRuleEloquentModel::withoutGlobalScope('tenant')->create([
                        'id' => Uuid::uuid4()->toString(),
                        'tenant_id' => $targetTenantId,
                        'category_id' => $sourceDiscount->category_id,
                        'pricing_class_id' => $newPricingClassId,
                        'min_days' => $sourceDiscount->min_days,
                        'min_duration_id' => $newDurationId,
                        'discount_type' => $sourceDiscount->discount_type,
                        'discount_value' => $sourceDiscount->discount_value,
                        'label' => $sourceDiscount->label,
                        'description' => $sourceDiscount->description,
                        'is_cumulative' => $sourceDiscount->is_cumulative,
                        'priority' => $sourceDiscount->priority,
                        'is_active' => true,
                    ]);

                    $imported['discount_rules']++;
                }
            }
        });

        $total = array_sum($imported);

        return new ImportDefaultPricingResponse(
            success: true,
            message: $total > 0
                ? "Successfully imported {$total} pricing items"
                : 'No new items to import (all already exist)',
            imported: $imported,
        );
    }

    /**
     * @param array<string, string> $pricingClassMap
     */
    private function buildPricingClassMap(string $sourceTenantId, string $targetTenantId, array &$pricingClassMap): void
    {
        $sourceClasses = PricingClassEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $sourceTenantId)
            ->get();

        $targetClasses = PricingClassEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $targetTenantId)
            ->get()
            ->keyBy('code');

        foreach ($sourceClasses as $sourceClass) {
            $targetClass = $targetClasses->get($sourceClass->code);
            if ($targetClass !== null) {
                $pricingClassMap[$sourceClass->id] = $targetClass->id;
            }
        }
    }

    /**
     * @param array<string, string> $durationMap
     */
    private function buildDurationMap(string $sourceTenantId, string $targetTenantId, array &$durationMap): void
    {
        $sourceDurations = DurationDefinitionEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $sourceTenantId)
            ->get();

        $targetDurations = DurationDefinitionEloquentModel::withoutGlobalScope('tenant')
            ->where('tenant_id', $targetTenantId)
            ->get()
            ->keyBy('code');

        foreach ($sourceDurations as $sourceDuration) {
            $targetDuration = $targetDurations->get($sourceDuration->code);
            if ($targetDuration !== null) {
                $durationMap[$sourceDuration->id] = $targetDuration->id;
            }
        }
    }
}
