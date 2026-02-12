<?php

declare(strict_types=1);

namespace Maintenance\Application\UpdateCustomMaintenanceReason;

use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;
use Maintenance\Domain\MaintenanceCategory;

final readonly class UpdateCustomMaintenanceReasonHandler
{
    public function __construct(
        private CustomMaintenanceReasonRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function handle(UpdateCustomMaintenanceReasonCommand $command): UpdateCustomMaintenanceReasonResponse
    {
        // Récupérer la raison existante
        $reason = $this->repository->findById($command->id);

        if ($reason === null) {
            throw CustomMaintenanceReasonNotFoundException::withId($command->id);
        }

        // Mettre à jour les informations
        $reason->updateInformation(
            label: $command->label,
            description: $command->description,
            category: MaintenanceCategory::from($command->category),
        );

        // Mettre à jour l'ordre de tri
        $reason->updateSortOrder($command->sortOrder);

        // Gérer l'état actif/inactif
        if ($command->isActive && !$reason->isActive()) {
            $reason->activate();
        } elseif (!$command->isActive && $reason->isActive()) {
            $reason->deactivate();
        }

        // Sauvegarder
        $this->repository->save($reason);

        return new UpdateCustomMaintenanceReasonResponse(
            id: $reason->id(),
            code: $reason->code(),
            label: $reason->label(),
            message: 'Custom maintenance reason updated successfully',
        );
    }
}
