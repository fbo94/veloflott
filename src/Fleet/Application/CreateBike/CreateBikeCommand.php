<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSizeLetter;
use Fleet\Domain\FrameSizeUnit;
use Fleet\Domain\WheelSize;

final readonly class CreateBikeCommand
{
    /**
     * @param string[] $photos
     */
    public function __construct(
        // Obligatoires
        public string $internalNumber,
        public string $modelId,
        public string $categoryId,
        public FrameSizeUnit $frameSizeUnit,
        public ?float $frameSizeNumeric,
        public ?FrameSizeLetter $frameSizeLetter,
        // Optionnels
        public ?int $year,
        public ?string $serialNumber,
        public ?string $color,
        public ?WheelSize $wheelSize,
        public ?int $frontSuspension,
        public ?int $rearSuspension,
        public ?BrakeType $brakeType,
        public ?float $purchasePrice,
        public ?\DateTimeImmutable $purchaseDate,
        public ?string $notes,
        public array $photos,
    ) {}
}
