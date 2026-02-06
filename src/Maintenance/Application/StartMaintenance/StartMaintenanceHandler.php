<?php

declare(strict_types=1);

namespace Maintenance\Application\StartMaintenance;

use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class StartMaintenanceHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {}

    public function handle(StartMaintenanceCommand $command): StartMaintenanceResponse
    {
        // Récupérer la maintenance
        $maintenance = $this->maintenanceRepository->findById($command->maintenanceId);

        // Démarrer la maintenance (appel au domain)
        $maintenance->start();

        // Sauvegarder la maintenance
        $this->maintenanceRepository->save($maintenance);

        return new StartMaintenanceResponse(
            maintenanceId: $maintenance->id(),
            message: 'Maintenance started successfully',
        );
    }
}
