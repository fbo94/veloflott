<?php

declare(strict_types=1);

use Fleet\Interface\Http\CreateBike\CreateBikeController;
use Fleet\Interface\Http\CreateBrand\CreateBrandController;
use Fleet\Interface\Http\CreateCategory\CreateCategoryController;
use Fleet\Interface\Http\CreateModel\CreateModelController;
use Fleet\Interface\Http\DeleteBikePhoto\DeleteBikePhotoController;
use Fleet\Interface\Http\DeleteBrand\DeleteBrandController;
use Fleet\Interface\Http\DeleteCategory\DeleteCategoryController;
use Fleet\Interface\Http\DeleteModel\DeleteModelController;
use Fleet\Interface\Http\DeleteRate\DeleteRateController;
use Fleet\Interface\Http\GetBikeDetail\GetBikeDetailController;
use Fleet\Interface\Http\GetBikeRate\GetBikeRateController;
use Fleet\Interface\Http\GetModelDetail\GetModelDetailController;
use Fleet\Interface\Http\ListBikes\ListBikesController;
use Fleet\Interface\Http\ListBrands\ListBrandsController;
use Fleet\Interface\Http\ListCategories\ListCategoriesController;
use Fleet\Interface\Http\ListModels\ListModelsController;
use Fleet\Interface\Http\ListRates\ListRatesController;
use Fleet\Interface\Http\SetBikeRate\SetBikeRateController;
use Fleet\Interface\Http\SetCategoryRate\SetCategoryRateController;
use Fleet\Interface\Http\UpdateBike\UpdateBikeController;
use Fleet\Interface\Http\UpdateBrand\UpdateBrandController;
use Fleet\Interface\Http\UpdateCategory\UpdateCategoryController;
use Fleet\Interface\Http\UpdateModel\UpdateModelController;
use Fleet\Interface\Http\UpdateRate\UpdateRateController;
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
});
