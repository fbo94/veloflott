<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Traits;

use Illuminate\Database\Eloquent\Builder;
use Tenant\Application\TenantContext;

/**
 * Trait SiteScoped - Filtrage automatique par site.
 *
 * Ce trait ajoute automatiquement:
 * - Un global scope qui filtre toutes les requêtes par site_id (si un site est dans le contexte)
 * - L'assignation automatique du site_id lors de la création (si un site est dans le contexte)
 *
 * Différence avec TenantScoped:
 * - TenantScoped est OBLIGATOIRE (toutes les données sont isolées par tenant)
 * - SiteScoped est OPTIONNEL (le filtrage ne s'applique que si un site est sélectionné)
 *
 * Usage:
 *   class BikeEloquentModel extends Model
 *   {
 *       use TenantScoped, SiteScoped;
 *   }
 *
 * IMPORTANT: La table doit avoir une colonne `site_id` (UUID, FK vers sites, NULLABLE).
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait SiteScoped
{
    /**
     * Boot du trait - ajoute le global scope optionnel.
     */
    public static function bootSiteScoped(): void
    {
        // Ajouter le global scope pour filtrer par site (seulement si un site est défini)
        static::addGlobalScope('site', function (Builder $builder): void {
            $tenantContext = app(TenantContext::class);

            if ($tenantContext->hasSite()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.site_id',
                    $tenantContext->getSiteId()
                );
            }
        });

        // Auto-assigner le site_id lors de la création (si un site est dans le contexte)
        static::creating(function ($model): void {
            if ($model->site_id === null) {
                $tenantContext = app(TenantContext::class);

                if ($tenantContext->hasSite()) {
                    $model->site_id = $tenantContext->getSiteId();
                }
            }
        });
    }

    /**
     * Scope pour requêter sans le filtre site.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithoutSiteScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('site');
    }

    /**
     * Scope pour filtrer par un site spécifique.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeForSite(Builder $query, string $siteId): Builder
    {
        return $query->withoutGlobalScope('site')
            ->where($this->getTable() . '.site_id', $siteId);
    }

    /**
     * Scope pour filtrer les ressources sans site assigné.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeWithoutSite(Builder $query): Builder
    {
        return $query->withoutGlobalScope('site')
            ->whereNull($this->getTable() . '.site_id');
    }
}
