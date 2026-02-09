<?php

declare(strict_types=1);

namespace Pricing\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Pricing\Domain\DiscountRuleRepositoryInterface;
use Pricing\Domain\DurationDefinitionRepositoryInterface;
use Pricing\Domain\PricingClassRepositoryInterface;
use Pricing\Domain\PricingRateRepositoryInterface;
use Pricing\Domain\Services\PricingCalculator;
use Pricing\Domain\Services\PricingValidator;
use Pricing\Infrastructure\Persistence\EloquentDiscountRuleRepository;
use Pricing\Infrastructure\Persistence\EloquentDurationDefinitionRepository;
use Pricing\Infrastructure\Persistence\EloquentPricingClassRepository;
use Pricing\Infrastructure\Persistence\EloquentPricingRateRepository;

final class PricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(
            PricingClassRepositoryInterface::class,
            EloquentPricingClassRepository::class
        );

        $this->app->bind(
            DurationDefinitionRepositoryInterface::class,
            EloquentDurationDefinitionRepository::class
        );

        $this->app->bind(
            PricingRateRepositoryInterface::class,
            EloquentPricingRateRepository::class
        );

        $this->app->bind(
            DiscountRuleRepositoryInterface::class,
            EloquentDiscountRuleRepository::class
        );

        // Domain Services
        $this->app->singleton(PricingCalculator::class);
        $this->app->singleton(PricingValidator::class);
    }

    public function boot(): void
    {
        // Charger les routes
        $this->loadRoutesFrom(__DIR__.'/../Interface/Http/routes.php');
    }
}
