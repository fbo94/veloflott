<?php

declare(strict_types=1);

use Customer\Interface\Http\CreateCustomer\CreateCustomerController;
use Customer\Interface\Http\SearchCustomers\SearchCustomersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['keycloak'])->prefix('api/customers')->group(function () {
    Route::post('/', CreateCustomerController::class)
        ->middleware('permission:manage_customers');

    Route::get('/search', SearchCustomersController::class)
        ->middleware('permission:view_customers');
});
