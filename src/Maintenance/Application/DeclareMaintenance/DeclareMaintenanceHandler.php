<?php

declare(strict_types=1);

namespace Maintenance\Application\DeclareMaintenance;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Domain\MaintenanceType;
use Ramsey\Uuid\Uuid;

final readonly class DeclareMaintenanceHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {}

    /**
     * @throws MaintenanceException
     */
    public function handle(DeclareMaintenanceCommand $command): DeclareMaintenanceResponse
    {
        // Vérifier que le vélo existe
        $bike = $this->bikeRepository->findById($command->bikeId);

        // Vérifier que le vélo peut être mis en maintenance
        // On ne peut pas déclarer une maintenance si le vélo est :
        // - RENTED (en location)
        // - MAINTENANCE (déjà en maintenance)
        // - RETIRED (retiré du service)
        if (! in_array($bike->status(), [BikeStatus::AVAILABLE, BikeStatus::UNAVAILABLE], true)) {
            throw MaintenanceException::bikeNotAvailableForMaintenance(
                $command->bikeId,
                $bike->status()->value
            );
        }

        // Convertir les strings en enums
        $type = MaintenanceType::from($command->type);
        $reason = MaintenanceReason::from($command->reason);
        $priority = MaintenancePriority::from($command->priority);

        // Créer la maintenance
        $maintenance = Maintenance::declare(
            id: Uuid::uuid4()->toString(),
            bikeId: $command->bikeId,
            type: $type,
            reason: $reason,
            priority: $priority,
            description: $command->description,
            scheduledAt: $command->scheduledAt,
            photos: $command->photos,
        );

        // Sauvegarder la maintenance
        $this->maintenanceRepository->save($maintenance);

        // Mettre le vélo en statut MAINTENANCE
        $bike->changeStatus(BikeStatus::MAINTENANCE);
        $this->bikeRepository->save($bike);

        return new DeclareMaintenanceResponse(
            maintenanceId: $maintenance->id(),
            bikeId: $maintenance->bikeId(),
            message: 'Maintenance declared successfully'
        );
    }
}
