<?php

declare(strict_types=1);

namespace Auth\Infrastructure;

use Auth\Domain\UserRepositoryInterface;
use Auth\Infrastructure\Keycloak\KeycloakTokenValidator;
use Auth\Infrastructure\Keycloak\UserSyncService;
use Auth\Infrastructure\Persistence\EloquentUserRepository;
use Auth\Interface\Http\Middleware\CheckPermission;
use Auth\Interface\Http\Middleware\KeycloakAuthenticate;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Keycloak Token Validator (singleton car stateless)
        $this->app->singleton(KeycloakTokenValidator::class, function ($app) {
            return new KeycloakTokenValidator(
                keycloakUrl: config('services.keycloak.url'),
                realm: config('services.keycloak.realm'),
            );
        });

        // User Sync Service
        $this->app->singleton(UserSyncService::class);
    }

    public function boot(): void
    {
        // Enregistrer les middlewares
        $router = $this->app['router'];
        $router->aliasMiddleware('keycloak', KeycloakAuthenticate::class);
        $router->aliasMiddleware('permission', CheckPermission::class);

        // Charger les routes
        $this->loadRoutesFrom(__DIR__.'/../Interface/Http/routes.php');

        // Charger les migrations
        $this->loadMigrationsFrom(__DIR__.'/migrations');
    }
}
