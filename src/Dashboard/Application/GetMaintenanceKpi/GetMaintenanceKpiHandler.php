<?php

declare(strict_types=1);

namespace Dashboard\Application\GetMaintenanceKpi;

use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class GetMaintenanceKpiHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {
    }

    public function handle(GetMaintenanceKpiQuery $query): GetMaintenanceKpiResponse
    {
        // Par défaut : 30 derniers jours
        $dateTo = $query->dateTo ?? new \DateTimeImmutable();
        $dateFrom = $query->dateFrom ?? $dateTo->modify('-30 days');

        // Compteurs maintenances
        $maintenancesByStatus = $this->maintenanceRepository->countByStatus();
        $totalMaintenances = array_sum($maintenancesByStatus);

        return new GetMaintenanceKpiResponse(
            period: [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
                'days' => $dateFrom->diff($dateTo)->days + 1,
            ],
            totalCompleted: $maintenancesByStatus['completed'] ?? 0,
            totalInProgress: $maintenancesByStatus['in_progress'] ?? 0,
            totalTodo: $maintenancesByStatus['todo'] ?? 0,
            total: $totalMaintenances,
            byStatus: $maintenancesByStatus,
        );
    }
}
