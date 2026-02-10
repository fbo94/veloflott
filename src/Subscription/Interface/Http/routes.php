<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Subscription\Interface\Http\ListSubscriptionPlans\ListSubscriptionPlansController;

/*
|--------------------------------------------------------------------------
| Subscription Module Routes
|--------------------------------------------------------------------------
|
| Routes pour la gestion des plans d'abonnement.
|
*/

// Liste des plans d'abonnement actifs (pour le formulaire de création tenant)
Route::middleware(['keycloak'])
    ->prefix('api/subscription-plans')
    ->group(function () {
        Route::get('/', ListSubscriptionPlansController::class)
            ->name('subscription-plans.index');
    });
