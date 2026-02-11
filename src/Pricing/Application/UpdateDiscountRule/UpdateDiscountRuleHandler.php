<?php

declare(strict_types=1);

namespace Pricing\Application\UpdateDiscountRule;

use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Domain\DiscountType;

final readonly class UpdateDiscountRuleHandler
{
    public function __construct(
        private DiscountRuleRepositoryInterface $repository,
    ) {
    }

    public function handle(UpdateDiscountRuleCommand $command): UpdateDiscountRuleResponse
    {
        $discountRule = $this->repository->findById($command->id);

        if ($discountRule === null) {
            throw new \DomainException("DiscountRule with id {$command->id} not found");
        }

        $discountType = DiscountType::from($command->discountType);

        $discountRule->update(
            categoryId: $command->categoryId,
            pricingClassId: $command->pricingClassId,
            minDays: $command->minDays,
            minDurationId: $command->minDurationId,
            discountType: $discountType,
            discountValue: $command->discountValue,
            label: $command->label,
            description: $command->description,
            isCumulative: $command->isCumulative,
            priority: $command->priority,
        );

        if ($command->isActive && !$discountRule->isActive()) {
            $discountRule->activate();
        } elseif (!$command->isActive && $discountRule->isActive()) {
            $discountRule->deactivate();
        }

        $this->repository->save($discountRule);

        return UpdateDiscountRuleResponse::fromDomain($discountRule);
    }
}
