<?php

declare(strict_types=1);

namespace Dashboard\Infrastructure;

use Illuminate\Support\ServiceProvider;

final class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Pas de repositories à binder - Dashboard est en lecture seule
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__.'/../Interface/Http/routes.php');
    }
}
