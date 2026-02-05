<?php

declare(strict_types=1);

namespace Dashboard\Application\GetCentralizedAlerts;

use Fleet\Domain\BikeRepositoryInterface;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final readonly class GetCentralizedAlertsHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
        private MaintenanceRepositoryInterface $maintenanceRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(): GetCentralizedAlertsResponse
    {
        $alerts = [];

        // 1. Retours en retard (HIGH severity)
        $lateRentals = $this->rentalRepository->findLateRentals();
        foreach ($lateRentals as $rental) {
            $daysLate = $rental->expectedReturnDate()->diff(new \DateTimeImmutable())->days;
            $alerts[] = [
                'type' => 'late_return',
                'severity' => 'high',
                'rental_id' => $rental->id(),
                'customer_id' => $rental->customerId(),
                'bike_id' => $rental->items()[0]->bikeId() ?? null,
                'message' => sprintf('Retour en retard de %d jour(s)', $daysLate),
                'days_late' => $daysLate,
                'created_at' => $rental->expectedReturnDate()->format('Y-m-d H:i:s'),
            ];
        }

        // 2. Maintenances urgentes en attente (HIGH severity)
        $urgentMaintenances = $this->maintenanceRepository->findWithFilters(
            priority: \Maintenance\Domain\MaintenancePriority::URGENT,
            status: \Maintenance\Domain\MaintenanceStatus::TODO
        );
        foreach ($urgentMaintenances as $maintenance) {
            $alerts[] = [
                'type' => 'urgent_maintenance',
                'severity' => 'high',
                'maintenance_id' => $maintenance->id(),
                'bike_id' => $maintenance->bikeId(),
                'message' => sprintf('Maintenance urgente en attente: %s', $maintenance->reason()->label()),
                'scheduled_at' => $maintenance->scheduledAt()->format('Y-m-d H:i:s'),
                'created_at' => $maintenance->createdAt()->format('Y-m-d H:i:s'),
            ];
        }

        // 3. Vélos indisponibles longue durée (MEDIUM severity)
        $longUnavailable = $this->bikeRepository->findLongUnavailable(5);
        foreach ($longUnavailable as $bike) {
            $alerts[] = [
                'type' => 'bike_long_unavailable',
                'severity' => 'medium',
                'bike_id' => $bike['bike_id'],
                'internal_number' => $bike['internal_number'],
                'message' => sprintf('Vélo indisponible depuis %d jour(s)', $bike['days_unavailable']),
                'days_unavailable' => $bike['days_unavailable'],
                'created_at' => now()->subDays($bike['days_unavailable'])->format('Y-m-d H:i:s'),
            ];
        }

        // 4. Maintenances longue durée (MEDIUM severity)
        $longRunningMaintenances = $this->maintenanceRepository->findLongRunning(7);
        foreach ($longRunningMaintenances as $maintenance) {
            $severity = $maintenance['priority'] === 'urgent' ? 'high' : 'medium';
            $alerts[] = [
                'type' => 'maintenance_long_running',
                'severity' => $severity,
                'maintenance_id' => $maintenance['maintenance_id'],
                'bike_id' => $maintenance['bike_id'],
                'message' => sprintf('Maintenance en cours depuis %d jour(s)', $maintenance['days_in_progress']),
                'days_in_progress' => $maintenance['days_in_progress'],
                'priority' => $maintenance['priority'],
                'created_at' => now()->subDays($maintenance['days_in_progress'])->format('Y-m-d H:i:s'),
            ];
        }

        // Trier par sévérité puis par date
        usort($alerts, function ($a, $b) {
            $severityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            $severityCompare = $severityOrder[$a['severity']] <=> $severityOrder[$b['severity']];

            if ($severityCompare !== 0) {
                return $severityCompare;
            }

            return $b['created_at'] <=> $a['created_at'];
        });

        // Compter par sévérité
        $countsBySeverity = [
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($alerts as $alert) {
            $countsBySeverity[$alert['severity']]++;
        }

        return new GetCentralizedAlertsResponse(
            alerts: $alerts,
            countsBySeverity: $countsBySeverity,
            total: count($alerts),
        );
    }
}
