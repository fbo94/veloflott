<?php

declare(strict_types=1);

namespace Tenant\Domain;

interface SiteRepositoryInterface
{
    public function findById(string $id): ?Site;

    public function findBySlug(string $tenantId, string $slug): ?Site;

    /**
     * @return Site[]
     */
    public function findByTenantId(string $tenantId): array;

    /**
     * @return Site[]
     */
    public function findActiveByTenantId(string $tenantId): array;

    /**
     * @return Site[]
     */
    public function findByCity(string $tenantId, string $city): array;

    public function save(Site $site): void;

    public function delete(string $id): void;

    public function existsWithSlug(string $tenantId, string $slug): bool;

    public function existsWithSlugExcluding(string $tenantId, string $slug, string $excludeId): bool;

    /**
     * @return Site[]
     */
    public function findNearby(string $tenantId, float $latitude, float $longitude, float $radiusKm = 10.0): array;

    public function countByTenantId(string $tenantId): int;

    public function countActiveByTenantId(string $tenantId): int;
}
