<?php

declare(strict_types=1);

namespace Subscription\Domain;

interface SubscriptionPlanRepositoryInterface
{
    public function findById(string $id): ?SubscriptionPlan;

    public function findByName(string $name): ?SubscriptionPlan;

    /**
     * @return SubscriptionPlan[]
     */
    public function findAllActive(): array;

    /**
     * @return SubscriptionPlan[]
     */
    public function findAll(): array;

    public function save(SubscriptionPlan $plan): void;

    public function delete(string $id): void;
}
