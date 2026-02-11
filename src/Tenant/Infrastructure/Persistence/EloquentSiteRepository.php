<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence;

use Tenant\Domain\Site;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\SiteStatus;
use Tenant\Infrastructure\Persistence\Models\SiteEloquentModel;

final class EloquentSiteRepository implements SiteRepositoryInterface
{
    public function findById(string $id): ?Site
    {
        $model = SiteEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findBySlug(string $tenantId, string $slug): ?Site
    {
        $model = SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Site[]
     */
    public function findByTenantId(string $tenantId): array
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (SiteEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Site[]
     */
    public function findActiveByTenantId(string $tenantId): array
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('status', SiteStatus::ACTIVE->value)
            ->orderBy('name')
            ->get()
            ->map(fn (SiteEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Site[]
     */
    public function findByCity(string $tenantId, string $city): array
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('city', $city)
            ->orderBy('name')
            ->get()
            ->map(fn (SiteEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function save(Site $site): void
    {
        SiteEloquentModel::updateOrCreate(
            ['id' => $site->id()],
            [
                'tenant_id' => $site->tenantId(),
                'name' => $site->name(),
                'slug' => $site->slug(),
                'address' => $site->address(),
                'city' => $site->city(),
                'postal_code' => $site->postalCode(),
                'country' => $site->country(),
                'phone' => $site->phone(),
                'email' => $site->email(),
                'status' => $site->status()->value,
                'opening_hours' => $site->openingHours(),
                'settings' => $site->settings(),
                'latitude' => $site->latitude(),
                'longitude' => $site->longitude(),
            ]
        );
    }

    public function delete(string $id): void
    {
        SiteEloquentModel::destroy($id);
    }

    public function existsWithSlug(string $tenantId, string $slug): bool
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->exists();
    }

    public function existsWithSlugExcluding(string $tenantId, string $slug, string $excludeId): bool
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->where('id', '!=', $excludeId)
            ->exists();
    }

    /**
     * @return Site[]
     */
    public function findNearby(string $tenantId, float $latitude, float $longitude, float $radiusKm = 10.0): array
    {
        // Formule Haversine simplifiée pour PostgreSQL/MySQL
        // 6371 = rayon de la Terre en km
        $haversine = '
            (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(latitude))
            ))
        ';

        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('status', SiteStatus::ACTIVE->value)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("$haversine < ?", [$latitude, $longitude, $latitude, $radiusKm])
            ->orderByRaw("$haversine", [$latitude, $longitude, $latitude])
            ->get()
            ->map(fn (SiteEloquentModel $model) => $this->toDomain($model))
            ->all();
    }

    public function countByTenantId(string $tenantId): int
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)->count();
    }

    public function countActiveByTenantId(string $tenantId): int
    {
        return SiteEloquentModel::where('tenant_id', $tenantId)
            ->where('status', SiteStatus::ACTIVE->value)
            ->count();
    }

    private function toDomain(SiteEloquentModel $model): Site
    {
        return Site::reconstitute(
            id: $model->id,
            tenantId: $model->tenant_id,
            name: $model->name,
            slug: $model->slug,
            address: $model->address,
            city: $model->city,
            postalCode: $model->postal_code,
            country: $model->country,
            phone: $model->phone,
            email: $model->email,
            status: SiteStatus::from($model->status),
            openingHours: $model->opening_hours,
            settings: $model->settings,
            latitude: $model->latitude,
            longitude: $model->longitude,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}
