<?php

declare(strict_types=1);

use Rental\Interface\Http\CreateRental\CreateRentalController;
use Rental\Interface\Http\CheckInRental\CheckInRentalController;
use Rental\Interface\Http\CheckOutRental\CheckOutRentalController;
use Rental\Interface\Http\ListActiveRentals\ListActiveRentalsController;
use Rental\Interface\Http\ListRentals\ListRentalsController;
use Rental\Interface\Http\GetRentalDetail\GetRentalDetailController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/rentals')->group(function () {
    Route::post('/', CreateRentalController::class)
        ->middleware('permission:create_rentals');

    Route::get('/', ListRentalsController::class)
        ->middleware('permission:view_rentals');

    Route::get('/active', ListActiveRentalsController::class)
        ->middleware('permission:view_rentals');

    Route::get('/{id}', GetRentalDetailController::class)
        ->middleware('permission:view_rentals');

    Route::post('/{id}/checkin', CheckInRentalController::class)
        ->middleware('permission:create_rentals');

    Route::post('/{id}/checkout', CheckOutRentalController::class)
        ->middleware('permission:create_rentals');
});
