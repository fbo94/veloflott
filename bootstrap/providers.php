<?php

return [
    Tenant\Infrastructure\TenantServiceProvider::class,
    Auth\Infrastructure\AuthServiceProvider::class,
    Fleet\Infrastructure\FleetServiceProvider::class,
    Customer\Infrastructure\CustomerServiceProvider::class,
    Rental\Infrastructure\RentalServiceProvider::class,
    Maintenance\Infrastructure\MaintenanceServiceProvider::class,
    Dashboard\Infrastructure\DashboardServiceProvider::class,
    Pricing\Infrastructure\PricingServiceProvider::class,
];
