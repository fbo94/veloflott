<?php

declare(strict_types=1);

namespace Rental\Domain\Repository;

use Rental\Domain\RentalSettings;
use Rental\Domain\RentalSettingsId;

interface RentalSettingsRepositoryInterface
{
    public function save(RentalSettings $settings): void;

    public function findById(RentalSettingsId $id): ?RentalSettings;

    /**
     * Find settings for a specific scope (site > tenant > app)
     * Returns the most specific settings available
     */
    public function findByScope(?string $tenantId, ?string $siteId): ?RentalSettings;

    /**
     * Get effective settings using hierarchy resolution
     * Site settings > Tenant settings > App default
     */
    public function getEffectiveSettings(?string $tenantId = null, ?string $siteId = null): RentalSettings;

    public function findAppDefault(): ?RentalSettings;

    public function findByTenantId(string $tenantId): ?RentalSettings;

    public function findBySiteId(string $tenantId, string $siteId): ?RentalSettings;

    public function delete(RentalSettingsId $id): void;
}
