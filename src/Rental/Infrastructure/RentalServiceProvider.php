<?php

declare(strict_types=1);

namespace Rental\Infrastructure;

use Rental\Domain\RentalRepositoryInterface;
use Rental\Infrastructure\Persistence\EloquentRentalRepository;
use Illuminate\Support\ServiceProvider;

final class RentalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RentalRepositoryInterface::class,
            EloquentRentalRepository::class
        );
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__.'/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }
}
