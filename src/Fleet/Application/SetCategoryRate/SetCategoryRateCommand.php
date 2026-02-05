<?php

declare(strict_types=1);

namespace Fleet\Application\SetCategoryRate;

final readonly class SetCategoryRateCommand
{
    public function __construct(
        public string $categoryId,
        public ?float $halfDayPrice,
        public float $dayPrice,
        public ?float $weekendPrice,
        public ?float $weekPrice,
    ) {
    }
}
