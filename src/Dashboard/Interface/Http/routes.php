<?php

declare(strict_types=1);

use Dashboard\Interface\Http\GetFleetOverview\GetFleetOverviewController;
use Dashboard\Interface\Http\GetUtilizationKpi\GetUtilizationKpiController;
use Dashboard\Interface\Http\GetRevenueKpi\GetRevenueKpiController;
use Dashboard\Interface\Http\GetMaintenanceKpi\GetMaintenanceKpiController;
use Dashboard\Interface\Http\GetTopBikes\GetTopBikesController;
use Dashboard\Interface\Http\GetTodayActivity\GetTodayActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/dashboard')->group(function () {
    // US 6.1 : Vue d'ensemble de la flotte
    Route::get('/overview', GetFleetOverviewController::class)
        ->middleware('permission:view_dashboard');

    // US 6.2 : Activité du jour
    Route::get('/today', GetTodayActivityController::class)
        ->middleware('permission:view_dashboard');

    // US 6.3 : Indicateurs de performance (KPIs) - Séparés pour performance
    Route::get('/kpis/utilization', GetUtilizationKpiController::class)
        ->middleware('permission:view_dashboard');

    Route::get('/kpis/revenue', GetRevenueKpiController::class)
        ->middleware('permission:view_dashboard');

    Route::get('/kpis/maintenance', GetMaintenanceKpiController::class)
        ->middleware('permission:view_dashboard');

    Route::get('/kpis/top-bikes', GetTopBikesController::class)
        ->middleware('permission:view_dashboard');
});
