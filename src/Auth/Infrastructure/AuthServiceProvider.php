<?php

declare(strict_types=1);

namespace Auth\Infrastructure;

use Auth\Domain\UserRepositoryInterface;
use Auth\Infrastructure\Keycloak\KeycloakOAuthService;
use Auth\Infrastructure\Keycloak\KeycloakTokenValidator;
use Auth\Infrastructure\Keycloak\UserSyncService;
use Auth\Infrastructure\Persistence\EloquentUserRepository;
use Auth\Interface\Http\Middleware\CheckPermission;
use Auth\Interface\Http\Middleware\KeycloakAuthenticate;
use Auth\Interface\Http\Middleware\RequireSuperAdmin;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->singleton(KeycloakTokenValidator::class, function ($app) {
            return new KeycloakTokenValidator(
                keycloakUrl: config('services.keycloak.url'),
                keycloakUrlInternal: config('services.keycloak.url_private'),
                realm: config('services.keycloak.realm'),
            );
        });

        $this->app->singleton(KeycloakOAuthService::class, function ($app) {
            return new KeycloakOAuthService(
                keycloakUrl: config('services.keycloak.url'),
                keycloakUrlPrivate: config('services.keycloak.url_private'),
                realm: config('services.keycloak.realm'),
                clientId: config('services.keycloak.client_id'),
                clientSecret: config('services.keycloak.client_secret'),
                redirectUri: config('services.keycloak.redirect_uri'),
            );
        });

        $this->app->singleton(UserSyncService::class);
    }

    public function boot(): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('keycloak', KeycloakAuthenticate::class);
        $router->aliasMiddleware('permission', CheckPermission::class);
        $router->aliasMiddleware('super-admin', RequireSuperAdmin::class);

        // Charger les routes
        $this->loadRoutesFrom(__DIR__ . '/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }
}
