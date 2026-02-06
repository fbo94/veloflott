<?php

declare(strict_types=1);

namespace Fleet\Infrastructure;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;
use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\DiscountRuleRepositoryInterface;
use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Fleet\Domain\ModelRepositoryInterface;
use Fleet\Domain\PricingClassRepositoryInterface;
use Fleet\Domain\PricingRateRepositoryInterface;
use Fleet\Domain\RateRepositoryInterface;
use Fleet\Domain\RentalPricingSnapshotRepositoryInterface;
use Fleet\Domain\Services\PricingCalculator;
use Fleet\Domain\Services\PricingValidator;
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

        // Pricing System Repositories
        $this->app->bind(
            PricingClassRepositoryInterface::class,
            EloquentPricingClassRepository::class
        );

        // Note: Other pricing repositories (Duration, Rate, Discount, Snapshot)
        // will be implemented following the same pattern as PricingClass

        // Domain Services
        $this->app->singleton(PricingCalculator::class);
        $this->app->singleton(PricingValidator::class);
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
