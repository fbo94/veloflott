<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure;

use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Infrastructure\Persistence\EloquentMaintenanceRepository;
use Illuminate\Support\ServiceProvider;

final class MaintenanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            MaintenanceRepositoryInterface::class,
            EloquentMaintenanceRepository::class
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
