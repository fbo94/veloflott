<?php

declare(strict_types=1);

use Fleet\Interface\Http\CreateBike\CreateBikeController;
use Fleet\Interface\Http\CreateCategory\CreateCategoryController;
use Fleet\Interface\Http\DeleteCategory\DeleteCategoryController;
use Fleet\Interface\Http\DeleteRate\DeleteRateController;
use Fleet\Interface\Http\GetBikeDetail\GetBikeDetailController;
use Fleet\Interface\Http\ListBikes\ListBikesController;
use Fleet\Interface\Http\ListCategories\ListCategoriesController;
use Fleet\Interface\Http\ListRates\ListRatesController;
use Fleet\Interface\Http\SetCategoryRate\SetCategoryRateController;
use Fleet\Interface\Http\UpdateCategory\UpdateCategoryController;
use Fleet\Interface\Http\UpdateRate\UpdateRateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/fleet')->group(function () {
    // Gestion des vélos
    Route::post('/bikes', CreateBikeController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/bikes', ListBikesController::class)
        ->middleware('permission:view_bikes');

    Route::get('/bikes/{id}', GetBikeDetailController::class)
        ->middleware('permission:view_bikes');

    // Gestion des catégories (US 1.6)
    Route::post('/categories', CreateCategoryController::class)
        ->middleware('permission:manage_categories');

    Route::get('/categories', ListCategoriesController::class)
        ->middleware('permission:view_bikes');

    Route::put('/categories/{id}', UpdateCategoryController::class)
        ->middleware('permission:manage_categories');

    Route::delete('/categories/{id}', DeleteCategoryController::class)
        ->middleware('permission:manage_categories');

    // Gestion des tarifs (US 1.7)
    Route::post('/categories/{categoryId}/rates', SetCategoryRateController::class)
        ->middleware('permission:manage_rates');

    Route::get('/rates', ListRatesController::class)
        ->middleware('permission:view_bikes');

    Route::put('/rates/{id}', UpdateRateController::class)
        ->middleware('permission:manage_rates');

    Route::delete('/rates/{id}', DeleteRateController::class)
        ->middleware('permission:manage_rates');
});
