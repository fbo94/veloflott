<?php

declare(strict_types=1);

namespace Subscription\Application\ListSubscriptionPlans;

use Subscription\Domain\SubscriptionPlanRepositoryInterface;

final readonly class ListSubscriptionPlansHandler
{
    public function __construct(
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
    ) {
    }

    public function handle(): ListSubscriptionPlansResponse
    {
        $plans = $this->subscriptionPlanRepository->findAllActive();

        return new ListSubscriptionPlansResponse($plans);
    }
}
