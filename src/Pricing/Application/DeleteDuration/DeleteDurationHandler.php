<?php

declare(strict_types=1);

namespace Pricing\Application\DeleteDuration;

use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Domain\Services\PricingValidator;

final readonly class DeleteDurationHandler
{
    public function __construct(
        private DurationDefinitionRepositoryInterface $repository,
        private PricingValidator $validator,
    ) {
    }

    public function handle(DeleteDurationCommand $command): void
    {
        $duration = $this->repository->findById($command->id);

        if ($duration === null) {
            throw new \DomainException("Duration with id {$command->id} not found");
        }

        if (!$this->validator->canDeleteDuration($command->id)) {
            throw new \DomainException('Cannot delete duration: active pricing rates exist for this duration');
        }

        $this->repository->delete($command->id);
    }
}
