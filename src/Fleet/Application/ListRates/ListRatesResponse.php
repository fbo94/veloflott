<?php

declare(strict_types=1);

namespace Fleet\Application\ListRates;

final readonly class ListRatesResponse
{
    /**
     * @param RateDto[] $rates
     */
    public function __construct(
        public array $rates,
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(
                fn (RateDto $rate) => $rate->toArray(),
                $this->rates
            ),
        ];
    }
}
