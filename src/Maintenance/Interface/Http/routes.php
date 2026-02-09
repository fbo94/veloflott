<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maintenance\Interface\Http\CompleteMaintenance\CompleteMaintenanceController;
use Maintenance\Interface\Http\DeclareMaintenance\DeclareMaintenanceController;
use Maintenance\Interface\Http\DeleteMaintenancePhoto\DeleteMaintenancePhotoController;
use Maintenance\Interface\Http\GetBikeMaintenanceHistory\GetBikeMaintenanceHistoryController;
use Maintenance\Interface\Http\GetMaintenanceDetail\GetMaintenanceDetailController;
use Maintenance\Interface\Http\ListMaintenanceReasons\ListMaintenanceReasonsController;
use Maintenance\Interface\Http\ListMaintenances\ListMaintenancesController;
use Maintenance\Interface\Http\StartMaintenance\StartMaintenanceController;
use Maintenance\Interface\Http\UploadMaintenancePhoto\UploadMaintenancePhotoController;

Route::middleware(['keycloak', 'tenant', 'require.tenant'])->prefix('api/maintenance')->group(function () {
    // Référentiel : Lister toutes les catégories et raisons de maintenance disponibles
    Route::get('/reasons', ListMaintenanceReasonsController::class)
        ->middleware('permission:view_maintenances');

    // US 4.1 : Déclarer une maintenance
    Route::post('/maintenances', DeclareMaintenanceController::class)
        ->middleware('permission:manage_maintenances');

    // US 4.2 : Suivre les maintenances en cours
    Route::get('/maintenances', ListMaintenancesController::class)
        ->middleware('permission:view_maintenances');

    // Détail d'une maintenance
    Route::get('/maintenances/{id}', GetMaintenanceDetailController::class)
        ->middleware('permission:view_maintenances');

    // Bonus : Démarrer une maintenance (TODO → IN_PROGRESS)
    Route::put('/maintenances/{id}/start', StartMaintenanceController::class)
        ->middleware('permission:manage_maintenances');

    // US 4.3 : Clôturer une maintenance
    Route::put('/maintenances/{id}/complete', CompleteMaintenanceController::class)
        ->middleware('permission:manage_maintenances');

    // US 4.5 : Historique des maintenances d'un vélo
    Route::get('/bikes/{bikeId}/maintenances', GetBikeMaintenanceHistoryController::class)
        ->middleware('permission:view_maintenances');

    // Gestion des photos de maintenance
    Route::post('/maintenances/{id}/photos', UploadMaintenancePhotoController::class)
        ->middleware('permission:manage_maintenances');

    Route::delete('/maintenances/{id}/photos', DeleteMaintenancePhotoController::class)
        ->middleware('permission:manage_maintenances');
});
