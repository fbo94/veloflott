<?php

declare(strict_types=1);

namespace Rental\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\Repository\DepositRetentionConfigRepositoryInterface;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;
use Rental\Infrastructure\Persistence\EloquentDepositRetentionConfigRepository;
use Rental\Infrastructure\Persistence\EloquentRentalRepository;
use Rental\Infrastructure\Persistence\EloquentRentalSettingsRepository;
use Rental\Infrastructure\Services\EloquentBikeAvailabilityService;

final class RentalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            RentalRepositoryInterface::class,
            EloquentRentalRepository::class,
        );

        $this->app->bind(
            RentalSettingsRepositoryInterface::class,
            EloquentRentalSettingsRepository::class,
        );

        $this->app->bind(
            DepositRetentionConfigRepositoryInterface::class,
            EloquentDepositRetentionConfigRepository::class,
        );

        // Service bindings
        $this->app->bind(
            BikeAvailabilityServiceInterface::class,
            EloquentBikeAvailabilityService::class,
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
