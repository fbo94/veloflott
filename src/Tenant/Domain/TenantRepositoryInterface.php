<?php

declare(strict_types=1);

namespace Tenant\Domain;

interface TenantRepositoryInterface
{
    /**
     * Trouve un tenant par son ID.
     */
    public function findById(string $id): ?Tenant;

    /**
     * Trouve un tenant par son slug.
     */
    public function findBySlug(string $slug): ?Tenant;

    /**
     * Trouve un tenant par son domaine.
     */
    public function findByDomain(string $domain): ?Tenant;

    /**
     * Retourne tous les tenants.
     *
     * @return Tenant[]
     */
    public function findAll(): array;

    /**
     * Retourne les tenants actifs uniquement.
     *
     * @return Tenant[]
     */
    public function findActive(): array;

    /**
     * Sauvegarde un tenant.
     */
    public function save(Tenant $tenant): void;

    /**
     * Supprime un tenant.
     */
    public function delete(string $id): void;

    /**
     * Vérifie si un slug existe déjà.
     */
    public function existsWithSlug(string $slug, ?string $excludeId = null): bool;

    /**
     * Vérifie si un domaine existe déjà.
     */
    public function existsWithDomain(string $domain, ?string $excludeId = null): bool;
}
