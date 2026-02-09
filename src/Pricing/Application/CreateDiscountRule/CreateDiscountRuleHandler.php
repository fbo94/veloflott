<?php

declare(strict_types=1);

namespace Pricing\Application\CreateDiscountRule;

use Pricing\Domain\DiscountRule;
use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Domain\DiscountType;

final readonly class CreateDiscountRuleHandler
{
    public function __construct(
        private DiscountRuleRepositoryInterface $repository,
    ) {
    }

    public function handle(CreateDiscountRuleCommand $command): CreateDiscountRuleResponse
    {
        $discountType = DiscountType::from($command->discountType);

        $discountRule = DiscountRule::create(
            id: $this->generateId(),
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

        $this->repository->save($discountRule);

        return CreateDiscountRuleResponse::fromDomain($discountRule);
    }

    private function generateId(): string
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
}
