<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tenant\Interface\Http\CreateTenant\CreateTenantController;
use Tenant\Interface\Http\ListTenants\ListTenantsController;
use Tenant\Interface\Http\RegisterTenant\RegisterTenantController;
use Tenant\Interface\Http\SiteController;
use Tenant\Interface\Http\UpdateTenant\UpdateTenantController;

/*
|--------------------------------------------------------------------------
| Tenant Module Routes
|--------------------------------------------------------------------------
|
| Routes pour la gestion des tenants et des sites (multi-tenant).
|
*/

// Route d'inscription publique (sans authentification)
Route::post('/api/register', RegisterTenantController::class)
    ->name('tenants.register');

// Routes tenants - Super Admin uniquement
Route::middleware(['keycloak', 'super-admin'])
    ->prefix('api/tenants')
    ->group(function () {
        Route::get('/', ListTenantsController::class)
            ->name('tenants.index');

        Route::post('/', CreateTenantController::class)
            ->name('tenants.store');

        Route::put('/{id}', UpdateTenantController::class)
            ->name('tenants.update')
            ->where('id', '[0-9a-f-]{36}');
    });

// Routes sites - Nécessitent authentification et contexte tenant
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
