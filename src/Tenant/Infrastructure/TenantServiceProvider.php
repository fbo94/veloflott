<?php

declare(strict_types=1);

namespace Tenant\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Infrastructure\Persistence\EloquentSiteRepository;
use Tenant\Infrastructure\Persistence\EloquentTenantRepository;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(
            TenantRepositoryInterface::class,
            EloquentTenantRepository::class
        );

        $this->app->bind(
            SiteRepositoryInterface::class,
            EloquentSiteRepository::class
        );
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
