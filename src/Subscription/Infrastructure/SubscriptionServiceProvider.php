<?php

declare(strict_types=1);

namespace Subscription\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Subscription\Infrastructure\Persistence\EloquentSubscriptionPlanRepository;

final class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SubscriptionPlanRepositoryInterface::class,
            EloquentSubscriptionPlanRepository::class
        );
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
