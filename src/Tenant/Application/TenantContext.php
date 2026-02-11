<?php

declare(strict_types=1);

namespace Tenant\Application;

use Tenant\Domain\Site;
use Tenant\Domain\Tenant;

/**
 * TenantContext - Service singleton qui maintient le contexte du tenant courant.
 *
 * Ce service est résolu une fois par requête HTTP et contient:
 * - Le Tenant courant (obligatoire après authentification)
 * - Le Site courant (optionnel, défini par header ou route)
 *
 * Usage:
 *   $context = app(TenantContext::class);
 *   $tenantId = $context->getTenantId();
 *   $siteId = $context->getSiteId(); // peut être null
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    private ?Site $site = null;

    // ===== Setters (utilisés par les middlewares) =====

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function setSite(Site $site): void
    {
        if ($this->tenant === null) {
            throw new \LogicException('Cannot set site without tenant context');
        }

        if (!$site->belongsToTenant($this->tenant->id())) {
            throw new \LogicException('Site does not belong to current tenant');
        }

        $this->site = $site;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->site = null;
    }

    // ===== Getters =====

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function site(): ?Site
    {
        return $this->site;
    }

    public function getTenantId(): ?string
    {
        return $this->tenant?->id();
    }

    public function getSiteId(): ?string
    {
        return $this->site?->id();
    }

    // ===== Checks =====

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function hasSite(): bool
    {
        return $this->site !== null;
    }

    public function isResolved(): bool
    {
        return $this->tenant !== null;
    }

    // ===== Required Getters (throw if not set) =====

    public function requireTenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new \RuntimeException('Tenant context is not resolved');
        }

        return $this->tenant;
    }

    public function requireTenantId(): string
    {
        return $this->requireTenant()->id();
    }

    public function requireSite(): Site
    {
        if ($this->site === null) {
            throw new \RuntimeException('Site context is not resolved');
        }

        return $this->site;
    }

    public function requireSiteId(): string
    {
        return $this->requireSite()->id();
    }

    // ===== Validation =====

    /**
     * Vérifie si une ressource appartient au tenant courant.
     */
    public function belongsToCurrentTenant(string $tenantId): bool
    {
        if ($this->tenant === null) {
            return false;
        }

        return $this->tenant->id() === $tenantId;
    }

    /**
     * Vérifie si une ressource appartient au site courant.
     */
    public function belongsToCurrentSite(string $siteId): bool
    {
        if ($this->site === null) {
            return false;
        }

        return $this->site->id() === $siteId;
    }
}
