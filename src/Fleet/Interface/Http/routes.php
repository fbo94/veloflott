<?php

declare(strict_types=1);

use Fleet\Interface\Http\CalculatePrice\CalculatePriceController;
use Fleet\Interface\Http\ChangeBikeStatus\ChangeBikeStatusController;
use Fleet\Interface\Http\CreateBike\CreateBikeController;
use Fleet\Interface\Http\CreateBrand\CreateBrandController;
use Fleet\Interface\Http\CreateCategory\CreateCategoryController;
use Fleet\Interface\Http\CreateDiscountRule\CreateDiscountRuleController;
use Fleet\Interface\Http\CreateDuration\CreateDurationController;
use Fleet\Interface\Http\CreateModel\CreateModelController;
use Fleet\Interface\Http\CreatePricingClass\CreatePricingClassController;
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
use Fleet\Interface\Http\ListDiscountRules\ListDiscountRulesController;
use Fleet\Interface\Http\ListDurations\ListDurationsController;
use Fleet\Interface\Http\ListModels\ListModelsController;
use Fleet\Interface\Http\ListPricingClasses\ListPricingClassesController;
use Fleet\Interface\Http\ListPricingRates\ListPricingRatesController;
use Fleet\Interface\Http\ListRates\ListRatesController;
use Fleet\Interface\Http\ResetSizeMappingConfiguration\ResetSizeMappingConfigurationController;
use Fleet\Interface\Http\RetireBike\RetireBikeController;
use Fleet\Interface\Http\SetBikeRate\SetBikeRateController;
use Fleet\Interface\Http\SetCategoryRate\SetCategoryRateController;
use Fleet\Interface\Http\SetPricingRate\SetPricingRateController;
use Fleet\Interface\Http\UpdateBike\UpdateBikeController;
use Fleet\Interface\Http\UpdateBrand\UpdateBrandController;
use Fleet\Interface\Http\UpdateCategory\UpdateCategoryController;
use Fleet\Interface\Http\UpdateModel\UpdateModelController;
use Fleet\Interface\Http\UpdatePricingClass\UpdatePricingClassController;
use Fleet\Interface\Http\UpdateRate\UpdateRateController;
use Fleet\Interface\Http\UpdateSizeMappingConfiguration\UpdateSizeMappingConfigurationController;
use Fleet\Interface\Http\UploadBikePhoto\UploadBikePhotoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/fleet')->group(function () {
    // Gestion des marques
    Route::post('/brands', CreateBrandController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/brands', ListBrandsController::class)
        ->middleware('permission:view_bikes');

    Route::put('/brands/{id}', UpdateBrandController::class)
        ->middleware('permission:manage_bikes');

    Route::delete('/brands/{id}', DeleteBrandController::class)
        ->middleware('permission:manage_bikes');

    // Gestion des modèles
    Route::post('/models', CreateModelController::class)
        ->middleware('permission:manage_bikes');

    Route::get('/models', ListModelsController::class)
        ->middleware('permission:view_bikes');

    Route::get('/models/{id}', GetModelDetailController::class)
        ->middleware('permission:view_bikes');

    Route::put('/models/{id}', UpdateModelController::class)
        ->middleware('permission:manage_bikes');

    Route::delete('/models/{id}', DeleteModelController::class)
        ->middleware('permission:manage_bikes');

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

    // Gestion des tarifs (US 1.7)
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

    // Gestion des correspondances de tailles
    Route::get('/size-mapping', GetActiveSizeMappingConfigurationController::class)
        ->middleware('permission:view_bikes');

    Route::put('/size-mapping', UpdateSizeMappingConfigurationController::class)
        ->middleware('permission:manage_bikes');

    Route::post('/size-mapping/reset', ResetSizeMappingConfigurationController::class)
        ->middleware('permission:manage_bikes');

    // Gestion du système de tarification 3D
    // Classes tarifaires
    Route::get('/pricing-classes', ListPricingClassesController::class)
        ->middleware('permission:view_bikes');

    Route::post('/pricing-classes', CreatePricingClassController::class)
        ->middleware('permission:manage_rates');

    Route::put('/pricing-classes/{pricingClassId}', UpdatePricingClassController::class)
        ->middleware('permission:manage_rates');

    // Durées
    Route::get('/durations', ListDurationsController::class)
        ->middleware('permission:view_bikes');

    Route::post('/durations', CreateDurationController::class)
        ->middleware('permission:manage_rates');

    // Grille tarifaire 3D (Catégorie × Classe × Durée)
    Route::get('/pricing-rates', ListPricingRatesController::class)
        ->middleware('permission:view_bikes');

    Route::post('/pricing-rates', SetPricingRateController::class)
        ->middleware('permission:manage_rates');

    // Règles de réduction
    Route::get('/discount-rules', ListDiscountRulesController::class)
        ->middleware('permission:view_bikes');

    Route::post('/discount-rules', CreateDiscountRuleController::class)
        ->middleware('permission:manage_rates');

    // Calcul de tarif
    Route::post('/pricing/calculate', CalculatePriceController::class)
        ->middleware('permission:view_bikes');
});
