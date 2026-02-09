<?php

declare(strict_types=1);

namespace Pricing\Application\BulkUpdatePricingRates;

use Pricing\Domain\PricingRate;
use Pricing\Domain\PricingRateRepositoryInterface;

final readonly class BulkUpdatePricingRatesHandler
{
    public function __construct(
        private PricingRateRepositoryInterface $pricingRateRepository,
    ) {
    }

    /**
     * @return array{created: int, updated: int}
     */
    public function handle(BulkUpdatePricingRatesCommand $command): array
    {
        $created = 0;
        $updated = 0;

        foreach ($command->rates as $rateData) {
            $existingRate = $this->pricingRateRepository->findByDimensions(
                $rateData['category_id'],
                $rateData['pricing_class_id'],
                $rateData['duration_id']
            );

            if ($existingRate !== null) {
                // Update existing rate
                $existingRate->updatePrice($rateData['price']);
                if (isset($rateData['is_active'])) {
                    if ($rateData['is_active']) {
                        $existingRate->activate();
                    } else {
                        $existingRate->deactivate();
                    }
                }
                $this->pricingRateRepository->save($existingRate);
                $updated++;
            } else {
                // Create new rate
                $newRate = new PricingRate(
                    id: $this->generateId(),
                    categoryId: $rateData['category_id'],
                    pricingClassId: $rateData['pricing_class_id'],
                    durationId: $rateData['duration_id'],
                    price: $rateData['price'],
                    isActive: $rateData['is_active'] ?? true,
                );
                $this->pricingRateRepository->save($newRate);
                $created++;
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    private function generateId(): string
    {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
    }
}
