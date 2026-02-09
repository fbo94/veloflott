<?php

declare(strict_types=1);

namespace Pricing\Application\UpdateDuration;

use Pricing\Domain\DurationDefinitionRepositoryInterface;

final readonly class UpdateDurationHandler
{
    public function __construct(
        private DurationDefinitionRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateDurationCommand $command): UpdateDurationResponse
    {
        $duration = $this->repository->findById($command->id);

        if ($duration === null) {
            throw new \DomainException("Duration with id {$command->id} not found");
        }

        $duration->update(
            label: $command->label,
            durationHours: $command->durationHours,
            durationDays: $command->durationDays,
            sortOrder: $command->sortOrder,
        );

        if ($command->isActive && ! $duration->isActive()) {
            $duration->activate();
        } elseif (! $command->isActive && $duration->isActive()) {
            $duration->deactivate();
        }

        $this->repository->save($duration);

        return UpdateDurationResponse::fromDomain($duration);
    }
}
