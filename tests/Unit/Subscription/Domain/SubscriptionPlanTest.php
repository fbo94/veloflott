<?php

declare(strict_types=1);

namespace Tests\Unit\Subscription\Domain;

use Subscription\Domain\SubscriptionPlan;

describe('SubscriptionPlan', function () {
    function createSubscriptionPlan(array $overrides = []): SubscriptionPlan
    {
        return new SubscriptionPlan(
            id: array_key_exists('id', $overrides) ? $overrides['id'] : 'plan-123',
            name: array_key_exists('name', $overrides) ? $overrides['name'] : 'premium',
            displayName: array_key_exists('displayName', $overrides) ? $overrides['displayName'] : 'Premium',
            description: array_key_exists('description', $overrides) ? $overrides['description'] : 'Premium plan description',
            priceMonthly: array_key_exists('priceMonthly', $overrides) ? $overrides['priceMonthly'] : 99.0,
            priceYearly: array_key_exists('priceYearly', $overrides) ? $overrides['priceYearly'] : 990.0,
            maxUsers: array_key_exists('maxUsers', $overrides) ? $overrides['maxUsers'] : 10,
            maxBikes: array_key_exists('maxBikes', $overrides) ? $overrides['maxBikes'] : 100,
            maxSites: array_key_exists('maxSites', $overrides) ? $overrides['maxSites'] : 5,
            features: array_key_exists('features', $overrides) ? $overrides['features'] : ['analytics' => true, 'api_access' => true],
            isActive: array_key_exists('isActive', $overrides) ? $overrides['isActive'] : true,
            sortOrder: array_key_exists('sortOrder', $overrides) ? $overrides['sortOrder'] : 1,
        );
    }

    describe('constructor', function () {
        it('creates a subscription plan with all properties', function () {
            $plan = createSubscriptionPlan();

            expect($plan->id())->toBe('plan-123');
            expect($plan->name())->toBe('premium');
            expect($plan->displayName())->toBe('Premium');
            expect($plan->description())->toBe('Premium plan description');
            expect($plan->priceMonthly())->toBe(99.0);
            expect($plan->priceYearly())->toBe(990.0);
            expect($plan->maxUsers())->toBe(10);
            expect($plan->maxBikes())->toBe(100);
            expect($plan->maxSites())->toBe(5);
            expect($plan->features())->toBe(['analytics' => true, 'api_access' => true]);
            expect($plan->isActive())->toBeTrue();
            expect($plan->sortOrder())->toBe(1);
        });

        it('creates a plan with null optional fields', function () {
            $plan = createSubscriptionPlan([
                'description' => null,
                'priceMonthly' => null,
                'priceYearly' => null,
                'features' => null,
            ]);

            expect($plan->description())->toBeNull();
            expect($plan->priceMonthly())->toBeNull();
            expect($plan->priceYearly())->toBeNull();
            expect($plan->features())->toBeNull();
        });

        it('creates timestamps on instantiation', function () {
            $plan = createSubscriptionPlan();

            expect($plan->createdAt())->toBeInstanceOf(\DateTimeImmutable::class);
            expect($plan->updatedAt())->toBeInstanceOf(\DateTimeImmutable::class);
        });
    });

    describe('activate', function () {
        it('activates an inactive plan', function () {
            $plan = createSubscriptionPlan(['isActive' => false]);

            $result = $plan->activate();

            expect($result)->toBe($plan);
            expect($plan->isActive())->toBeTrue();
        });

        it('updates the updatedAt timestamp', function () {
            $plan = createSubscriptionPlan(['isActive' => false]);
            $originalUpdatedAt = $plan->updatedAt();

            usleep(1000);
            $plan->activate();

            expect($plan->updatedAt())->not->toBe($originalUpdatedAt);
        });
    });

    describe('deactivate', function () {
        it('deactivates an active plan', function () {
            $plan = createSubscriptionPlan(['isActive' => true]);

            $result = $plan->deactivate();

            expect($result)->toBe($plan);
            expect($plan->isActive())->toBeFalse();
        });

        it('updates the updatedAt timestamp', function () {
            $plan = createSubscriptionPlan(['isActive' => true]);
            $originalUpdatedAt = $plan->updatedAt();

            usleep(1000);
            $plan->deactivate();

            expect($plan->updatedAt())->not->toBe($originalUpdatedAt);
        });
    });

    describe('free plan', function () {
        it('can represent a free plan with null prices', function () {
            $plan = createSubscriptionPlan([
                'name' => 'free',
                'displayName' => 'Free',
                'priceMonthly' => null,
                'priceYearly' => null,
                'maxUsers' => 1,
                'maxBikes' => 10,
                'maxSites' => 1,
            ]);

            expect($plan->priceMonthly())->toBeNull();
            expect($plan->priceYearly())->toBeNull();
            expect($plan->maxUsers())->toBe(1);
            expect($plan->maxBikes())->toBe(10);
        });
    });

    describe('enterprise plan', function () {
        it('can represent an enterprise plan with high limits', function () {
            $plan = createSubscriptionPlan([
                'name' => 'enterprise',
                'displayName' => 'Enterprise',
                'priceMonthly' => 999.0,
                'maxUsers' => 999,
                'maxBikes' => 9999,
                'maxSites' => 99,
                'features' => [
                    'analytics' => true,
                    'api_access' => true,
                    'white_label' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                ],
            ]);

            expect($plan->maxUsers())->toBe(999);
            expect($plan->maxBikes())->toBe(9999);
            expect($plan->features())->toHaveKey('white_label');
            expect($plan->features()['priority_support'])->toBeTrue();
        });
    });
});
