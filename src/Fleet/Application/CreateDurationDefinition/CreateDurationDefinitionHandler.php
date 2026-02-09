<?php

declare(strict_types=1);

namespace Fleet\Application\CreateDurationDefinition;

use Fleet\Domain\DurationDefinition;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreateDurationDefinitionHandler
{
    public function __construct(
        private DurationDefinitionRepositoryInterface $durationRepository,
    ) {
    }

    public function handle(CreateDurationDefinitionCommand $command): DurationDefinitionDto
    {
        // Vérifier l'unicité du code
        if ($this->durationRepository->existsWithCode($command->code)) {
            throw new \DomainException("Duration with code '{$command->code}' already exists");
        }

        // Créer la durée
        $duration = DurationDefinition::create(
            id: Uuid::uuid4()->toString(),
            code: $command->code,
            label: $command->label,
            durationHours: $command->durationHours,
            durationDays: $command->durationDays,
            isCustom: $command->isCustom,
            sortOrder: $command->sortOrder,
        );

        // Sauvegarder
        $this->durationRepository->save($duration);

        // Retourner le DTO
        return DurationDefinitionDto::fromDomain($duration);
    }
}
