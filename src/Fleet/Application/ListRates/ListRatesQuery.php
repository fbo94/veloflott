<?php

declare(strict_types=1);

namespace Fleet\Application\ListRates;

final readonly class ListRatesQuery
{
    public function __construct(
        public ?string $categoryId = null,
        public ?string $bikeId = null,
    ) {}
}
