<?php

declare(strict_types=1);

namespace Maintenance\Application\DeleteCustomMaintenanceReason;

use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;

final readonly class DeleteCustomMaintenanceReasonHandler
{
    public function __construct(
        private CustomMaintenanceReasonRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function handle(DeleteCustomMaintenanceReasonCommand $command): void
    {
        // Vérifier que la raison existe
        $reason = $this->repository->findById($command->id);

        if ($reason === null) {
            throw CustomMaintenanceReasonNotFoundException::withId($command->id);
        }

        // Supprimer
        $this->repository->delete($command->id);
    }
}
