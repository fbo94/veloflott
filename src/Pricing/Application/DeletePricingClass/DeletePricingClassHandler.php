<?php

declare(strict_types=1);

namespace Pricing\Application\DeletePricingClass;

use Pricing\Domain\PricingClassRepositoryInterface;
use Pricing\Domain\Services\PricingValidator;

final readonly class DeletePricingClassHandler
{
    public function __construct(
        private PricingClassRepositoryInterface $repository,
        private PricingValidator $validator,
    ) {
    }

    public function handle(DeletePricingClassCommand $command): void
    {
        $pricingClass = $this->repository->findById($command->id);

        if ($pricingClass === null) {
            throw new \DomainException("PricingClass with id {$command->id} not found");
        }

        // Get bikes count for this pricing class (assuming 0 for now - should be injected from Fleet)
        $bikesCount = 0;

        if (! $this->validator->canDeletePricingClass($command->id, $bikesCount)) {
            throw new \DomainException('Cannot delete pricing class: bikes are assigned to this class');
        }

        $this->repository->delete($command->id);
    }
}
