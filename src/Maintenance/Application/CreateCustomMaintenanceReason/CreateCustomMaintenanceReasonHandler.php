<?php

declare(strict_types=1);

namespace Maintenance\Application\CreateCustomMaintenanceReason;

use Maintenance\Domain\CustomMaintenanceReason;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\MaintenanceCategory;
use Ramsey\Uuid\Uuid;

final readonly class CreateCustomMaintenanceReasonHandler
{
    public function __construct(
        private CustomMaintenanceReasonRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws MaintenanceException
     */
    public function handle(CreateCustomMaintenanceReasonCommand $command): CreateCustomMaintenanceReasonResponse
    {
        // Vérifier que le code n'existe pas déjà
        if ($this->repository->existsWithCode($command->code)) {
            throw new MaintenanceException("Custom maintenance reason with code '{$command->code}' already exists.");
        }

        // Récupérer le tenantId depuis le contexte (assume tenant middleware)
        $tenantId = app('tenant')->id;

        // Créer la raison personnalisée
        $reason = new CustomMaintenanceReason(
            id: Uuid::uuid4()->toString(),
            tenantId: $tenantId,
            code: $command->code,
            label: $command->label,
            description: $command->description,
            category: MaintenanceCategory::from($command->category),
            isActive: $command->isActive,
            sortOrder: $command->sortOrder,
        );

        // Sauvegarder
        $this->repository->save($reason);

        return new CreateCustomMaintenanceReasonResponse(
            id: $reason->id(),
            code: $reason->code(),
            label: $reason->label(),
            message: 'Custom maintenance reason created successfully',
        );
    }
}
