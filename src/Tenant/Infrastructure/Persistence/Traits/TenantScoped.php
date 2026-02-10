<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Traits;

use Illuminate\Database\Eloquent\Builder;
use Tenant\Application\TenantContext;

/**
 * Trait TenantScoped - Filtrage automatique par tenant.
 *
 * Ce trait ajoute automatiquement:
 * - Un global scope qui filtre toutes les requêtes par tenant_id
 * - L'assignation automatique du tenant_id lors de la création
 *
 * Usage:
 *   class BikeEloquentModel extends Model
 *   {
 *       use TenantScoped;
 *   }
 *
 * IMPORTANT: La table doit avoir une colonne `tenant_id` (UUID, FK vers tenants).
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait TenantScoped
{
    /**
     * Boot du trait - ajoute le global scope.
     */
    public static function bootTenantScoped(): void
    {
        // Ajouter le global scope pour filtrer par tenant
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantContext = app(TenantContext::class);

            if ($tenantContext->hasTenant()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.tenant_id',
                    $tenantContext->getTenantId()
                );
            }
        });

        // Auto-assigner le tenant_id lors de la création
        static::creating(function ($model): void {
            if ($model->tenant_id === null) {
                $tenantContext = app(TenantContext::class);

                if ($tenantContext->hasTenant()) {
                    $model->tenant_id = $tenantContext->getTenantId();
                } else {
                    // Pas de tenant context : lancer une exception claire
                    $table = $model->getTable();
                    $user = request()->user();

                    $message = "Cannot create {$table} record without tenant context.";
                    if ($user !== null && $user->isSuperAdmin()) {
                        $message .= " Super Admin: Please provide X-Tenant-Id header to specify the target tenant.";
                    } else {
                        $message .= " Please provide a valid tenant context.";
                    }

                    throw new \RuntimeException($message);
                }
            }
        });
    }

    /**
     * Scope pour requêter sans le filtre tenant (usage admin).
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    /**
     * Scope pour filtrer par un tenant spécifique.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->withoutGlobalScope('tenant')
            ->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
