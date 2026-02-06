<?php

declare(strict_types=1);

namespace Maintenance\Application\CompleteMaintenance;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class CompleteMaintenanceHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {}

    public function handle(CompleteMaintenanceCommand $command): CompleteMaintenanceResponse
    {
        // Récupérer la maintenance
        $maintenance = $this->maintenanceRepository->findById($command->maintenanceId);

        // Compléter la maintenance (appel au domain)
        $maintenance->complete(
            workDescription: $command->workDescription,
            partsReplaced: $command->partsReplaced,
            cost: $command->cost,
        );

        // Ajouter les photos si fournies
        foreach ($command->photos as $photoUrl) {
            $maintenance->addPhoto($photoUrl);
        }

        // Sauvegarder la maintenance
        $this->maintenanceRepository->save($maintenance);

        // Remettre le vélo en statut AVAILABLE
        $bike = $this->bikeRepository->findById($maintenance->bikeId());
        $bike->changeStatus(BikeStatus::AVAILABLE);
        $this->bikeRepository->save($bike);

        return new CompleteMaintenanceResponse(
            maintenanceId: $maintenance->id(),
            bikeId: $maintenance->bikeId(),
            message: 'Maintenance completed successfully',
        );
    }
}
