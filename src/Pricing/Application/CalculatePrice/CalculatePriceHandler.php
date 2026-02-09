<?php

declare(strict_types=1);

namespace Pricing\Application\CalculatePrice;

use Pricing\Domain\Services\PricingCalculator;

final readonly class CalculatePriceHandler
{
    public function __construct(
        private PricingCalculator $pricingCalculator,
    ) {}

    public function handle(CalculatePriceCommand $command): PriceCalculationDto
    {
        // Calculer le prix
        $calculation = $this->pricingCalculator->calculate(
            categoryId: $command->categoryId,
            pricingClassId: $command->pricingClassId,
            durationId: $command->durationId,
            customDays: $command->customDays,
        );

        // Retourner le DTO
        return PriceCalculationDto::fromDomain($calculation);
    }
}
