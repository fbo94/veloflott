<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence;

use Tenant\Domain\Tenant;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

final class EloquentTenantRepository implements TenantRepositoryInterface
{
    public function findById(string $id): ?Tenant
    {
        $model = TenantEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $slug): ?Tenant
    {
        $model = TenantEloquentModel::where('slug', $slug)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $model = TenantEloquentModel::where('domain', $domain)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Tenant[]
     */
    public function findAll(?string $status = null, ?string $search = null): array
    {
        $query = TenantEloquentModel::query();

        // Filtrer par statut si fourni
        if ($status !== null) {
            $query->where('status', $status);
        }

        // Recherche textuelle si fournie
        if ($search !== null && $search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%")
                    ->orWhere('contact_email', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn (TenantEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Tenant[]
     */
    public function findActive(): array
    {
        return TenantEloquentModel::where('status', TenantStatus::ACTIVE->value)
            ->orderBy('name')
            ->get()
            ->map(fn (TenantEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(Tenant $tenant): void
    {
        TenantEloquentModel::updateOrCreate(
            ['id' => $tenant->id()],
            [
                'name' => $tenant->name(),
                'slug' => $tenant->slug(),
                'domain' => $tenant->domain(),
                'status' => $tenant->status()->value,
                'contact_email' => $tenant->contactEmail(),
                'contact_phone' => $tenant->contactPhone(),
                'settings' => $tenant->settings(),
                'address' => $tenant->address(),
                'logo_url' => $tenant->logoUrl(),
                'subscription_plan_id' => $tenant->subscriptionPlanId(),
                'max_users' => $tenant->maxUsers(),
                'max_bikes' => $tenant->maxBikes(),
                'max_sites' => $tenant->maxSites(),
                'trial_ends_at' => $tenant->trialEndsAt(),
                'onboarding_completed' => $tenant->onboardingCompleted(),
            ]
        );
    }

    public function delete(string $id): void
    {
        TenantEloquentModel::destroy($id);
    }

    public function existsWithSlug(string $slug, ?string $excludeId = null): bool
    {
        $query = TenantEloquentModel::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existsWithDomain(string $domain, ?string $excludeId = null): bool
    {
        $query = TenantEloquentModel::where('domain', $domain);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    private function toDomain(TenantEloquentModel $model): Tenant
    {
        return new Tenant(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            domain: $model->domain,
            status: TenantStatus::from($model->status),
            contactEmail: $model->contact_email,
            contactPhone: $model->contact_phone,
            settings: $model->settings,
            address: $model->address,
            logoUrl: $model->logo_url,
            subscriptionPlanId: $model->subscription_plan_id,
            maxUsers: $model->max_users ?? 5,
            maxBikes: $model->max_bikes ?? 50,
            maxSites: $model->max_sites ?? 1,
            trialEndsAt: $model->trial_ends_at,
            onboardingCompleted: $model->onboarding_completed ?? false,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
