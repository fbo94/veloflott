<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tenant\Interface\Http\SiteController;

/*
|--------------------------------------------------------------------------
| Tenant Module Routes
|--------------------------------------------------------------------------
|
| Routes pour la gestion des sites (multi-tenant).
| Toutes les routes nécessitent l'authentification Keycloak et le contexte tenant.
|
*/

Route::middleware(['keycloak', 'tenant', 'require.tenant'])
    ->prefix('api/sites')
    ->group(function () {
        // Liste des sites du tenant courant
        Route::get('/', [SiteController::class, 'index'])
            ->name('sites.index');

        // Détail d'un site
        Route::get('/{id}', [SiteController::class, 'show'])
            ->name('sites.show')
            ->where('id', '[0-9a-f-]{36}');

        // Création d'un site (admin tenant seulement)
        Route::post('/', [SiteController::class, 'store'])
            ->middleware('permission:manage_sites')
            ->name('sites.store');

        // Mise à jour d'un site (admin tenant seulement)
        Route::put('/{id}', [SiteController::class, 'update'])
            ->middleware('permission:manage_sites')
            ->name('sites.update')
            ->where('id', '[0-9a-f-]{36}');

        // Changement de statut d'un site (admin tenant seulement)
        Route::put('/{id}/status', [SiteController::class, 'changeStatus'])
            ->middleware('permission:manage_sites')
            ->name('sites.change-status')
            ->where('id', '[0-9a-f-]{36}');

        // Suppression d'un site (admin tenant seulement)
        Route::delete('/{id}', [SiteController::class, 'destroy'])
            ->middleware('permission:manage_sites')
            ->name('sites.destroy')
            ->where('id', '[0-9a-f-]{36}');
    });
