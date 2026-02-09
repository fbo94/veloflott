<?php

declare(strict_types=1);

namespace Pricing\Application\DeleteDiscountRule;

use Pricing\Domain\DiscountRuleRepositoryInterface;

final readonly class DeleteDiscountRuleHandler
{
    public function __construct(
        private DiscountRuleRepositoryInterface $repository,
    ) {
    }

    public function handle(DeleteDiscountRuleCommand $command): void
    {
        $discountRule = $this->repository->findById($command->id);

        if ($discountRule === null) {
            throw new \DomainException("DiscountRule with id {$command->id} not found");
        }

        $this->repository->delete($command->id);
    }
}
