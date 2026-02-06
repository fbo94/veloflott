<?php

declare(strict_types=1);

namespace Maintenance\Application\GetBikeMaintenanceHistory;

use Fleet\Domain\BikeRepositoryInterface;
use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class GetBikeMaintenanceHistoryHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {}

    public function handle(GetBikeMaintenanceHistoryQuery $query): GetBikeMaintenanceHistoryResponse
    {
        // Vérifier que le vélo existe
        $bike = $this->bikeRepository->findById($query->bikeId);

        // Récupérer l'historique des maintenances pour ce vélo
        $maintenances = $this->maintenanceRepository->findByBikeId($query->bikeId);

        return new GetBikeMaintenanceHistoryResponse(
            bikeId: $bike->id(),
            bikeInternalNumber: $bike->internalNumber(),
            maintenances: $maintenances,
            total: count($maintenances),
        );
    }
}
