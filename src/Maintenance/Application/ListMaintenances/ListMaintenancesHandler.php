<?php

declare(strict_types=1);

namespace Maintenance\Application\ListMaintenances;

use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceStatus;

final readonly class ListMaintenancesHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {
    }

    public function handle(ListMaintenancesQuery $query): ListMaintenancesResponse
    {
        // Convertir les filtres string en enums si fournis
        $status = $query->status !== null ? MaintenanceStatus::from($query->status) : null;
        $priority = $query->priority !== null ? MaintenancePriority::from($query->priority) : null;
        $dateFrom = $query->dateFrom !== null ? new \DateTimeImmutable($query->dateFrom) : null;
        $dateTo = $query->dateTo !== null ? new \DateTimeImmutable($query->dateTo) : null;

        // Récupérer les maintenances filtrées
        $maintenances = $this->maintenanceRepository->findWithFilters(
            bikeId: $query->bikeId,
            status: $status,
            priority: $priority,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
        );

        // Récupérer le compte par statut
        $countsByStatus = $this->maintenanceRepository->countByStatus();

        return new ListMaintenancesResponse(
            maintenances: $maintenances,
            countsByStatus: $countsByStatus,
            total: count($maintenances),
        );
    }
}
