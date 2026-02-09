<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBike;

final readonly class UpdateBikeCommand
{
    /**
     * @param  string[]  $photos
     */
    public function __construct(
        public string $bikeId,
        public string $modelId,
        public string $categoryId,
        public string $frameSizeUnit,
        public ?float $frameSizeNumeric,
        public ?string $frameSizeLetter,
        public ?int $year,
        public ?string $serialNumber,
        public ?string $color,
        public ?string $wheelSize,
        public ?int $frontSuspension,
        public ?int $rearSuspension,
        public ?string $brakeType,
        public ?float $purchasePrice,
        public ?string $purchaseDate,
        public ?string $notes,
        public array $photos,
        public ?string $pricingClassId = null,
    ) {
    }
}
