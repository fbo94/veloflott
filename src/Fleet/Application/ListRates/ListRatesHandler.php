<?php

declare(strict_types=1);

namespace Fleet\Application\ListRates;

use Fleet\Domain\RateRepositoryInterface;

final class ListRatesHandler
{
    public function __construct(
        private readonly RateRepositoryInterface $rates,
    ) {
    }

    public function handle(ListRatesQuery $query): ListRatesResponse
    {
        if ($query->categoryId !== null) {
            $rates = $this->rates->findByCategoryId($query->categoryId);
        } elseif ($query->bikeId !== null) {
            $rates = $this->rates->findByBikeId($query->bikeId);
        } else {
            $rates = $this->rates->findAll();
        }

        $rateDtos = array_map(
            fn ($rate) => RateDto::fromRate($rate),
            $rates
        );

        return new ListRatesResponse($rateDtos);
    }
}
