<?php

return [
    App\Providers\AppServiceProvider::class,
    Auth\Infrastructure\AuthServiceProvider::class,
    Fleet\Infrastructure\FleetServiceProvider::class,
    Customer\Infrastructure\CustomerServiceProvider::class,
    Rental\Infrastructure\RentalServiceProvider::class,
];
