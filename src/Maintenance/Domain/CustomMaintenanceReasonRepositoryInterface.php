<?php

declare(strict_types=1);

namespace Maintenance\Domain;

interface CustomMaintenanceReasonRepositoryInterface
{
    /**
     * Trouve une raison personnalisée par son ID.
     */
    public function findById(string $id): ?CustomMaintenanceReason;

    /**
     * Trouve une raison personnalisée par son code.
     */
    public function findByCode(string $code): ?CustomMaintenanceReason;

    /**
     * Retourne toutes les raisons personnalisées actives.
     *
     * @return CustomMaintenanceReason[]
     */
    public function findAllActive(?MaintenanceCategory $category = null): array;

    /**
     * Retourne toutes les raisons personnalisées.
     *
     * @return CustomMaintenanceReason[]
     */
    public function findAll(?MaintenanceCategory $category = null, ?bool $isActive = null): array;

    /**
     * Sauvegarde une raison personnalisée.
     */
    public function save(CustomMaintenanceReason $reason): void;

    /**
     * Supprime une raison personnalisée.
     */
    public function delete(string $id): void;

    /**
     * Vérifie si un code existe déjà.
     */
    public function existsWithCode(string $code, ?string $excludeId = null): bool;
}
