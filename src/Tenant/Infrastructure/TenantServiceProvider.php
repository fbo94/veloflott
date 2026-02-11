<?php

declare(strict_types=1);

namespace Tenant\Infrastructure;

use GuzzleHttp\Client;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Tenant\Application\TenantContext;
use Tenant\Domain\SiteRepositoryInterface;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Infrastructure\Keycloak\KeycloakAdminService;
use Tenant\Infrastructure\Persistence\EloquentSiteRepository;
use Tenant\Infrastructure\Persistence\EloquentTenantRepository;
use Tenant\Interface\Http\Middleware\RequireSiteContext;
use Tenant\Interface\Http\Middleware\RequireTenantContext;
use Tenant\Interface\Http\Middleware\ResolveSiteMiddleware;
use Tenant\Interface\Http\Middleware\ResolveTenantMiddleware;

final class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TenantContext as singleton (shared per request)
        $this->app->singleton(TenantContext::class);

        // Repository bindings
        $this->app->bind(
            TenantRepositoryInterface::class,
            EloquentTenantRepository::class
        );

        $this->app->bind(
            SiteRepositoryInterface::class,
            EloquentSiteRepository::class
        );

        // Keycloak Admin Service
        $this->app->singleton(KeycloakAdminService::class, function ($app) {
            return new KeycloakAdminService(
                httpClient: new Client([
                    'timeout' => 10,
                    'verify' => false,  // Désactiver la vérification SSL en mode dev
                    'http_errors' => true,
                ]),
                logger: $app->make(LoggerInterface::class),
                keycloakUrl: config('services.keycloak.url_private', config('services.keycloak.url')),
                realm: config('services.keycloak.realm'),
                adminUsername: config('services.keycloak.admin_username'),
                adminPassword: config('services.keycloak.admin_password'),
            );
        });
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../Interface/Http/routes.php');

        // Register middleware aliases
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('tenant', ResolveTenantMiddleware::class);
        $router->aliasMiddleware('site', ResolveSiteMiddleware::class);
        $router->aliasMiddleware('require.tenant', RequireTenantContext::class);
        $router->aliasMiddleware('require.site', RequireSiteContext::class);
    }
}
