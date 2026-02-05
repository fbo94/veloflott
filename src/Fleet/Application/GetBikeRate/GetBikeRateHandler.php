<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeRate;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\Services\RateResolver;

final class GetBikeRateHandler
{
    public function __construct(
        private readonly BikeRepositoryInterface $bikes,
        private readonly RateResolver $rateResolver,
    ) {
    }

    public function handle(GetBikeRateQuery $query): GetBikeRateResponse
    {
        // Récupérer le vélo
        $bike = $this->bikes->findById($query->bikeId);
        if ($bike === null) {
            throw new \DomainException("Le vélo '{$query->bikeId}' n'existe pas.");
        }

        // Résoudre le tarif applicable
        $rate = $this->rateResolver->resolveForBike($bike);

        return GetBikeRateResponse::fromRate($rate, $bike);
    }
}
