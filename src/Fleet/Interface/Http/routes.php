<?php

declare(strict_types=1);

use Fleet\Interface\Http\CreateBike\CreateBikeController;
use Fleet\Interface\Http\GetBikeDetail\GetBikeDetailController;
use Fleet\Interface\Http\ListBikes\ListBikesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/fleet')->group(function () {
    // Gestion des vélos
    Route::post('/bikes', CreateBikeController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/bikes', ListBikesController::class)
        ->middleware('permission:view_bikes');

    Route::get('/bikes/{id}', GetBikeDetailController::class)
        ->middleware('permission:view_bikes');
});
