<?php

declare(strict_types=1);

namespace Maintenance\Domain;

interface MaintenanceRepositoryInterface
{
    public function save(Maintenance $maintenance): void;

    public function findById(string $id): Maintenance;

    /**
     * @return Maintenance[]
     */
    public function findByBikeId(string $bikeId): array;

    /**
     * @return Maintenance[]
     */
    public function findInProgress(): array;

    /**
     * @return Maintenance[]
     */
    public function findByStatus(MaintenanceStatus $status): array;

    /**
     * @return Maintenance[]
     */
    public function findWithFilters(
        ?string $bikeId = null,
        ?MaintenanceStatus $status = null,
        ?MaintenancePriority $priority = null,
        ?\DateTimeImmutable $dateFrom = null,
        ?\DateTimeImmutable $dateTo = null,
    ): array;

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array;

    /**
     * Trouve les maintenances planifiées à une date donnée
     * @return Maintenance[]
     */
    public function findScheduledOnDate(\DateTimeImmutable $date): array;

    /**
     * Trouve les maintenances complétées à une date donnée
     * @return Maintenance[]
     */
    public function findCompletedOnDate(\DateTimeImmutable $date): array;

    /**
     * Compte le nombre de maintenances urgentes en attente (TODO + URGENT)
     */
    public function countUrgentPending(): int;

    public function delete(string $id): void;
}
