<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Maintenance\Domain\CustomMaintenanceReasonRepositoryInterface;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Infrastructure\Persistence\EloquentCustomMaintenanceReasonRepository;
use Maintenance\Infrastructure\Persistence\EloquentMaintenanceRepository;

final class MaintenanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            MaintenanceRepositoryInterface::class,
            EloquentMaintenanceRepository::class
        );

        $this->app->bind(
            CustomMaintenanceReasonRepositoryInterface::class,
            EloquentCustomMaintenanceReasonRepository::class
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
