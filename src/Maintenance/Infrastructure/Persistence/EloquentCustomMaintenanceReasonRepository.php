<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure\Persistence;

use Maintenance\Domain\CustomMaintenanceReason;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;
use Maintenance\Domain\MaintenanceCategory;

final class EloquentCustomMaintenanceReasonRepository implements CustomMaintenanceReasonRepositoryInterface
{
    public function findById(string $id): ?CustomMaintenanceReason
    {
        $model = CustomMaintenanceReasonEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByCode(string $code): ?CustomMaintenanceReason
    {
        $model = CustomMaintenanceReasonEloquentModel::where('code', $code)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return CustomMaintenanceReason[]
     */
    public function findAllActive(?MaintenanceCategory $category = null): array
    {
        $query = CustomMaintenanceReasonEloquentModel::where('is_active', true);

        if ($category !== null) {
            $query->where('category', $category->value);
        }

        return $query->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (CustomMaintenanceReasonEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return CustomMaintenanceReason[]
     */
    public function findAll(?MaintenanceCategory $category = null, ?bool $isActive = null): array
    {
        $query = CustomMaintenanceReasonEloquentModel::query();

        if ($category !== null) {
            $query->where('category', $category->value);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->orderBy('sort_order')
            ->orderBy('label')
            ->get()
            ->map(fn (CustomMaintenanceReasonEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(CustomMaintenanceReason $reason): void
    {
        CustomMaintenanceReasonEloquentModel::updateOrCreate(
            ['id' => $reason->id()],
            [
                'tenant_id' => $reason->tenantId(),
                'code' => $reason->code(),
                'label' => $reason->label(),
                'description' => $reason->description(),
                'category' => $reason->category()->value,
                'is_active' => $reason->isActive(),
                'sort_order' => $reason->sortOrder(),
            ]
        );
    }

    public function delete(string $id): void
    {
        CustomMaintenanceReasonEloquentModel::destroy($id);
    }

    public function existsWithCode(string $code, ?string $excludeId = null): bool
    {
        $query = CustomMaintenanceReasonEloquentModel::where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(CustomMaintenanceReasonEloquentModel $model): CustomMaintenanceReason
    {
        return new CustomMaintenanceReason(
            id: $model->id,
            tenantId: $model->tenant_id,
            code: $model->code,
            label: $model->label,
            description: $model->description,
            category: $model->category,
            isActive: $model->is_active,
            sortOrder: $model->sort_order,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
