<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pricing\Interface\Http\BulkUpdatePricingRates\BulkUpdatePricingRatesController;
use Pricing\Interface\Http\CalculatePrice\CalculatePriceController;
use Pricing\Interface\Http\CreateDiscountRule\CreateDiscountRuleController;
use Pricing\Interface\Http\CreateDuration\CreateDurationController;
use Pricing\Interface\Http\CreatePricingClass\CreatePricingClassController;
use Pricing\Interface\Http\DeleteDiscountRule\DeleteDiscountRuleController;
use Pricing\Interface\Http\DeleteDuration\DeleteDurationController;
use Pricing\Interface\Http\DeletePricingClass\DeletePricingClassController;
use Pricing\Interface\Http\ImportDefaultPricing\ImportDefaultPricingController;
use Pricing\Interface\Http\ListDefaultDiscountRules\ListDefaultDiscountRulesController;
use Pricing\Interface\Http\ListDefaultDurations\ListDefaultDurationsController;
use Pricing\Interface\Http\ListDefaultPricingClasses\ListDefaultPricingClassesController;
use Pricing\Interface\Http\ListDefaultPricingRates\ListDefaultPricingRatesController;
use Pricing\Interface\Http\ListDiscountRules\ListDiscountRulesController;
use Pricing\Interface\Http\ListDurations\ListDurationsController;
use Pricing\Interface\Http\ListPricingClasses\ListPricingClassesController;
use Pricing\Interface\Http\ListPricingRates\ListPricingRatesController;
use Pricing\Interface\Http\UpdateDiscountRule\UpdateDiscountRuleController;
use Pricing\Interface\Http\UpdateDuration\UpdateDurationController;
use Pricing\Interface\Http\UpdatePricingClass\UpdatePricingClassController;

Route::middleware(['keycloak', 'tenant', 'require.tenant'])->prefix('api/pricing')->group(function () {
    // Classes tarifaires
    Route::get('/classes', ListPricingClassesController::class)
        ->middleware('permission:view_bikes');

    Route::post('/classes', CreatePricingClassController::class)
        ->middleware('permission:manage_rates');

    Route::put('/classes/{id}', UpdatePricingClassController::class)
        ->middleware('permission:manage_rates');

    Route::delete('/classes/{id}', DeletePricingClassController::class)
        ->middleware('permission:manage_rates');

    // Durées
    Route::get('/durations', ListDurationsController::class)
        ->middleware('permission:view_bikes');

    Route::post('/durations', CreateDurationController::class)
        ->middleware('permission:manage_rates');

    Route::put('/durations/{id}', UpdateDurationController::class)
        ->middleware('permission:manage_rates');

    Route::delete('/durations/{id}', DeleteDurationController::class)
        ->middleware('permission:manage_rates');

    // Grille tarifaire 3D (Catégorie × Classe × Durée)
    Route::get('/rates', ListPricingRatesController::class)
        ->middleware('permission:view_bikes');

    Route::put('/rates', BulkUpdatePricingRatesController::class)
        ->middleware('permission:manage_rates');

    // Règles de réduction
    Route::get('/discounts', ListDiscountRulesController::class)
        ->middleware('permission:view_bikes');

    Route::post('/discounts', CreateDiscountRuleController::class)
        ->middleware('permission:manage_rates');

    Route::put('/discounts/{id}', UpdateDiscountRuleController::class)
        ->middleware('permission:manage_rates');

    Route::delete('/discounts/{id}', DeleteDiscountRuleController::class)
        ->middleware('permission:manage_rates');

    // Calcul de tarif
    Route::post('/calculate', CalculatePriceController::class)
        ->middleware('permission:view_bikes');

    // Import de la grille tarifaire par défaut
    Route::post('/import-defaults', ImportDefaultPricingController::class)
        ->middleware('permission:manage_rates');
});

/*
|--------------------------------------------------------------------------
| Default Pricing Templates (no tenant required)
|--------------------------------------------------------------------------
|
| Ces endpoints permettent aux tenants de récupérer les grilles tarifaires
| par défaut pour les utiliser comme templates lors de leur configuration.
|
*/
Route::middleware(['keycloak'])->prefix('api/pricing/defaults')->group(function () {
    Route::get('/classes', ListDefaultPricingClassesController::class);
    Route::get('/durations', ListDefaultDurationsController::class);
    Route::get('/rates', ListDefaultPricingRatesController::class);
    Route::get('/discounts', ListDefaultDiscountRulesController::class);
});
