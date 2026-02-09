<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Rental\Interface\Http\CancelRental\CancelRentalController;
use Rental\Interface\Http\CheckInRental\CheckInRentalController;
use Rental\Interface\Http\CheckOutRental\CheckOutRentalController;
use Rental\Interface\Http\CreateRental\CreateRentalController;
use Rental\Interface\Http\CreateReservation\CreateReservationController;
use Rental\Interface\Http\EarlyReturn\EarlyReturnController;
use Rental\Interface\Http\GetBikeAvailability\GetAvailableBikesController;
use Rental\Interface\Http\GetBikeAvailability\GetBikeAvailabilityController;
use Rental\Interface\Http\GetBikeRentals\GetBikeRentalsController;
use Rental\Interface\Http\GetRentalDetail\GetRentalDetailController;
use Rental\Interface\Http\ListActiveRentals\ListActiveRentalsController;
use Rental\Interface\Http\ListRentals\ListRentalsController;
use Rental\Interface\Http\RentalSettings\GetRentalSettingsController;
use Rental\Interface\Http\RentalSettings\UpdateRentalSettingsController;

Route::middleware(['keycloak'])->prefix('api/rentals')->group(function () {
    // Create rental (immediate - client present)
    Route::post('/', CreateRentalController::class)
        ->middleware('permission:create_rentals');

    // Create reservation (future date)
    Route::post('/reserve', CreateReservationController::class)
        ->middleware('permission:create_rentals');

    Route::get('/', ListRentalsController::class)
        ->middleware('permission:view_rentals');

    Route::get('/active', ListActiveRentalsController::class)
        ->middleware('permission:view_rentals');

    Route::get('/{id}', GetRentalDetailController::class)
        ->middleware('permission:view_rentals')
        ->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

    Route::post('/{id}/checkin', CheckInRentalController::class)
        ->middleware('permission:create_rentals');

    Route::post('/{id}/checkout', CheckOutRentalController::class)
        ->middleware('permission:create_rentals');

    // Early return (before expected return date)
    Route::post('/{id}/early-return', EarlyReturnController::class)
        ->middleware('permission:create_rentals');

    Route::post('/{id}/cancel', CancelRentalController::class)
        ->middleware('permission:create_rentals');

    Route::get('/bikes/{bikeId}', GetBikeRentalsController::class)
        ->middleware('permission:view_rentals');
});

// Bike availability endpoints
Route::middleware(['keycloak'])->prefix('api/bikes')->group(function () {
    // Get available bikes for a period
    Route::get('/available', GetAvailableBikesController::class)
        ->middleware('permission:view_bikes');

    // Get bike availability calendar
    Route::get('/{id}/availability', GetBikeAvailabilityController::class)
        ->middleware('permission:view_bikes');
});

// Rental settings endpoints
Route::middleware(['keycloak'])->prefix('api/settings/rental')->group(function () {
    Route::get('/', GetRentalSettingsController::class)
        ->middleware('permission:view_settings');

    Route::put('/', UpdateRentalSettingsController::class)
        ->middleware('permission:manage_settings');
});
