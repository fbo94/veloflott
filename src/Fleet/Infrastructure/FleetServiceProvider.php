<?php

declare(strict_types=1);

namespace Fleet\Infrastructure;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;
use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\ModelRepositoryInterface;
use Fleet\Domain\PricingClassRepositoryInterface;
use Fleet\Domain\RateRepositoryInterface;
use Fleet\Domain\SizeMappingConfigurationRepositoryInterface;
use Fleet\Infrastructure\Persistence\EloquentBikeRepository;
use Fleet\Infrastructure\Persistence\EloquentBikeStatusHistoryRepository;
use Fleet\Infrastructure\Persistence\EloquentBrandRepository;
use Fleet\Infrastructure\Persistence\EloquentCategoryRepository;
use Fleet\Infrastructure\Persistence\EloquentModelRepository;
use Fleet\Infrastructure\Persistence\EloquentPricingClassRepository;
use Fleet\Infrastructure\Persistence\EloquentRateRepository;
use Fleet\Infrastructure\Persistence\EloquentSizeMappingConfigurationRepository;
use Illuminate\Support\ServiceProvider;

final class FleetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            BrandRepositoryInterface::class,
            EloquentBrandRepository::class
        );

        $this->app->bind(
            ModelRepositoryInterface::class,
            EloquentModelRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );

        $this->app->bind(
            PricingClassRepositoryInterface::class,
            EloquentPricingClassRepository::class
        );

        $this->app->bind(
            RateRepositoryInterface::class,
            EloquentRateRepository::class
        );

        $this->app->bind(
            BikeRepositoryInterface::class,
            EloquentBikeRepository::class
        );

        $this->app->bind(
            BikeStatusHistoryRepositoryInterface::class,
            EloquentBikeStatusHistoryRepository::class
        );

        $this->app->bind(
            SizeMappingConfigurationRepositoryInterface::class,
            EloquentSizeMappingConfigurationRepository::class
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
