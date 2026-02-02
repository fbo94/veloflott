<?php

declare(strict_types=1);

use Auth\Interface\Http\Authorize\AuthorizeController;
use Auth\Interface\Http\GetAuthorizationUrl\GetAuthorizationUrlController;
use Auth\Interface\Http\GetCurrentUser\GetCurrentUserController;
use Auth\Interface\Http\ListUsers\ListUsersController;
use Auth\Interface\Http\ToggleUserStatus\ToggleUserStatusController;
use Auth\Interface\Http\UpdateUserRole\UpdateUserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Module Routes
|--------------------------------------------------------------------------
|
| Routes pour le module d'authentification et gestion des utilisateurs.
|
*/

Route::prefix('api/auth')->group(function () {
    // OAuth2 - Endpoints publics (pas d'authentification requise)
    Route::get('/authorization-url', GetAuthorizationUrlController::class)
        ->name('auth.authorization-url');

    Route::post('/authorize', AuthorizeController::class)
        ->name('auth.authorize');
});

Route::middleware(['keycloak'])->prefix('api')->group(function () {
    // Récupérer l'utilisateur courant
    Route::get('/me', GetCurrentUserController::class);

    // Gestion des utilisateurs (admin uniquement)
    Route::middleware(['permission:manage_users'])->prefix('users')->group(function () {
        Route::get('/', ListUsersController::class);
        Route::put('/{id}/role', UpdateUserRoleController::class);
        Route::post('/{id}/toggle-status', ToggleUserStatusController::class);
    });
});
