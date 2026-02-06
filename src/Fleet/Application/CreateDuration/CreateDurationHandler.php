<?php

declare(strict_types=1);

namespace Fleet\Application\CreateDuration;

use Fleet\Domain\DurationDefinition;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreateDurationHandler
{
    public function __construct(
        private DurationDefinitionRepositoryInterface $repository,
    ) {}

    public function handle(CreateDurationCommand $command): CreateDurationResponse
    {
        $duration = DurationDefinition::create(
            id: Uuid::uuid4()->toString(),
            code: $command->code,
            label: $command->label,
            durationHours: $command->durationHours,
            durationDays: $command->durationDays,
            isCustom: $command->isCustom,
            sortOrder: $command->sortOrder,
        );

        $this->repository->save($duration);

        return CreateDurationResponse::fromDomain($duration);
    }
}
