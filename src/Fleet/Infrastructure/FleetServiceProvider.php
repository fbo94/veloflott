<?php

declare(strict_types=1);

namespace Fleet\Infrastructure;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\CategoryRepositoryInterface;
use Fleet\Domain\ModelRepositoryInterface;
use Fleet\Domain\RateRepositoryInterface;
use Fleet\Infrastructure\Persistence\EloquentBikeRepository;
use Fleet\Infrastructure\Persistence\EloquentBrandRepository;
use Fleet\Infrastructure\Persistence\EloquentCategoryRepository;
use Fleet\Infrastructure\Persistence\EloquentModelRepository;
use Fleet\Infrastructure\Persistence\EloquentRateRepository;
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
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
