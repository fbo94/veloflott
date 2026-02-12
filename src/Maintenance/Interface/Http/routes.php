<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Maintenance\Interface\Http\CompleteMaintenance\CompleteMaintenanceController;
use Maintenance\Interface\Http\CreateCustomMaintenanceReason\CreateCustomMaintenanceReasonController;
use Maintenance\Interface\Http\DeclareMaintenance\DeclareMaintenanceController;
use Maintenance\Interface\Http\DeleteCustomMaintenanceReason\DeleteCustomMaintenanceReasonController;
use Maintenance\Interface\Http\DeleteMaintenancePhoto\DeleteMaintenancePhotoController;
use Maintenance\Interface\Http\GetBikeMaintenanceHistory\GetBikeMaintenanceHistoryController;
use Maintenance\Interface\Http\GetCustomMaintenanceReasonDetail\GetCustomMaintenanceReasonDetailController;
use Maintenance\Interface\Http\GetMaintenanceDetail\GetMaintenanceDetailController;
use Maintenance\Interface\Http\ListCustomMaintenanceReasons\ListCustomMaintenanceReasonsController;
use Maintenance\Interface\Http\ListMaintenanceReasons\ListMaintenanceReasonsController;
use Maintenance\Interface\Http\ListMaintenances\ListMaintenancesController;
use Maintenance\Interface\Http\StartMaintenance\StartMaintenanceController;
use Maintenance\Interface\Http\UpdateCustomMaintenanceReason\UpdateCustomMaintenanceReasonController;
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

    // CRUD des raisons de maintenance personnalisées (Custom Maintenance Reasons)
    // Lister toutes les raisons personnalisées
    Route::get('/custom-reasons', ListCustomMaintenanceReasonsController::class)
        ->middleware('permission:view_maintenances');

    // Obtenir le détail d'une raison personnalisée
    Route::get('/custom-reasons/{id}', GetCustomMaintenanceReasonDetailController::class)
        ->middleware('permission:view_maintenances');

    // Créer une raison personnalisée
    Route::post('/custom-reasons', CreateCustomMaintenanceReasonController::class)
        ->middleware('permission:manage_maintenances');

    // Mettre à jour une raison personnalisée
    Route::put('/custom-reasons/{id}', UpdateCustomMaintenanceReasonController::class)
        ->middleware('permission:manage_maintenances');

    // Supprimer une raison personnalisée
    Route::delete('/custom-reasons/{id}', DeleteCustomMaintenanceReasonController::class)
        ->middleware('permission:manage_maintenances');
});
