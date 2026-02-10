<?php

declare(strict_types=1);

use Fleet\Interface\Http\ChangeBikeStatus\ChangeBikeStatusController;
use Fleet\Interface\Http\CreateBike\CreateBikeController;
use Fleet\Interface\Http\CreateBrand\CreateBrandController;
use Fleet\Interface\Http\CreateCategory\CreateCategoryController;
use Fleet\Interface\Http\CreateModel\CreateModelController;
use Fleet\Interface\Http\DeleteBikePhoto\DeleteBikePhotoController;
use Fleet\Interface\Http\DeleteBrand\DeleteBrandController;
use Fleet\Interface\Http\DeleteCategory\DeleteCategoryController;
use Fleet\Interface\Http\DeleteModel\DeleteModelController;
use Fleet\Interface\Http\DeleteRate\DeleteRateController;
use Fleet\Interface\Http\GetActiveSizeMappingConfiguration\GetActiveSizeMappingConfigurationController;
use Fleet\Interface\Http\GetBikeDetail\GetBikeDetailController;
use Fleet\Interface\Http\GetBikeRate\GetBikeRateController;
use Fleet\Interface\Http\GetBikeStatusHistory\GetBikeStatusHistoryController;
use Fleet\Interface\Http\GetModelDetail\GetModelDetailController;
use Fleet\Interface\Http\ListBikes\ListBikesController;
use Fleet\Interface\Http\ListBrands\ListBrandsController;
use Fleet\Interface\Http\ListCategories\ListCategoriesController;
use Fleet\Interface\Http\ListModels\ListModelsController;
use Fleet\Interface\Http\ListRates\ListRatesController;
use Fleet\Interface\Http\ResetSizeMappingConfiguration\ResetSizeMappingConfigurationController;
use Fleet\Interface\Http\RetireBike\RetireBikeController;
use Fleet\Interface\Http\SetBikeRate\SetBikeRateController;
use Fleet\Interface\Http\SetCategoryRate\SetCategoryRateController;
use Fleet\Interface\Http\UpdateBike\UpdateBikeController;
use Fleet\Interface\Http\UpdateBrand\UpdateBrandController;
use Fleet\Interface\Http\UpdateCategory\UpdateCategoryController;
use Fleet\Interface\Http\UpdateModel\UpdateModelController;
use Fleet\Interface\Http\UpdateRate\UpdateRateController;
use Fleet\Interface\Http\UpdateSizeMappingConfiguration\UpdateSizeMappingConfigurationController;
use Fleet\Interface\Http\UploadBikePhoto\UploadBikePhotoController;
use Illuminate\Support\Facades\Route;

// Routes brands - Catalogue global (pas de tenant requis)
Route::middleware(['keycloak'])->prefix('api/fleet')->group(function () {
    Route::get('/brands', ListBrandsController::class)
        ->middleware('permission:view_bikes');

    Route::post('/brands', CreateBrandController::class)
        ->middleware('permission:manage_bikes');

    Route::put('/brands/{id}', UpdateBrandController::class)
        ->middleware('permission:manage_bikes');

    Route::delete('/brands/{id}', DeleteBrandController::class)
        ->middleware('permission:manage_bikes');
});

// Routes models - Catalogue global (super admin uniquement)
Route::middleware(['keycloak', 'super-admin'])->prefix('api/fleet')->group(function () {
    Route::post('/models', CreateModelController::class);
    Route::get('/models', ListModelsController::class);
    Route::get('/models/{id}', GetModelDetailController::class);
    Route::put('/models/{id}', UpdateModelController::class);
    Route::delete('/models/{id}', DeleteModelController::class);

    // Gestion des correspondances de tailles (configuration globale)
    Route::get('/size-mapping', GetActiveSizeMappingConfigurationController::class);
    Route::put('/size-mapping', UpdateSizeMappingConfigurationController::class);
    Route::post('/size-mapping/reset', ResetSizeMappingConfigurationController::class);
});

// Routes nécessitant un tenant
Route::middleware(['keycloak', 'tenant', 'require.tenant'])->prefix('api/fleet')->group(function () {

    // Gestion des vélos
    Route::post('/bikes', CreateBikeController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/bikes', ListBikesController::class)
        ->middleware('permission:view_bikes');

    Route::get('/bikes/{id}', GetBikeDetailController::class)
        ->middleware('permission:view_bikes');

    Route::put('/bikes/{id}', UpdateBikeController::class)
        ->middleware('permission:manage_bikes');

    Route::post('/bikes/{id}/retire', RetireBikeController::class)
        ->middleware('permission:manage_bikes');

    Route::put('/bikes/{id}/status', ChangeBikeStatusController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/bikes/{id}/status-history', GetBikeStatusHistoryController::class)
        ->middleware('permission:view_bikes');

    Route::post('/bikes/{id}/photos', UploadBikePhotoController::class)
        ->middleware('permission:manage_bikes');

    Route::delete('/bikes/{id}/photos', DeleteBikePhotoController::class)
        ->middleware('permission:manage_bikes');

    // Gestion des catégories (US 1.6)
    Route::post('/categories', CreateCategoryController::class)
        ->middleware('permission:manage_categories');

    Route::get('/categories', ListCategoriesController::class)
        ->middleware('permission:view_bikes');

    Route::put('/categories/{id}', UpdateCategoryController::class)
        ->middleware('permission:manage_categories');

    Route::delete('/categories/{id}', DeleteCategoryController::class)
        ->middleware('permission:manage_categories');

    // Gestion des tarifs (US 1.7) - Legacy, utilisez /api/pricing pour le système 3D
    Route::post('/rates/categories', SetCategoryRateController::class)
        ->middleware('permission:manage_rates');

    Route::post('/rates/bikes', SetBikeRateController::class)
        ->middleware('permission:manage_rates');

    Route::get('/bikes/{bikeId}/rate', GetBikeRateController::class)
        ->middleware('permission:view_bikes');

    Route::get('/rates', ListRatesController::class)
        ->middleware('permission:view_bikes');

    Route::put('/rates/{id}', UpdateRateController::class)
        ->middleware('permission:manage_rates');

    Route::delete('/rates/{id}', DeleteRateController::class)
        ->middleware('permission:manage_rates');

    // Note: Le système de tarification 3D (pricing-classes, durations, pricing-rates, discount-rules)
    // a été déplacé vers le bounded context Pricing.
    // Utilisez les endpoints /api/pricing/* à la place.
});
