<?php

declare(strict_types=1);

namespace Rental\Application\CheckInRental;

/**
 * DTO pour les données de check-in d'un vélo.
 */
final readonly class BikeCheckInData
{
    public function __construct(
        public string $bikeId,
        public int $clientHeight,
        public int $clientWeight,
        public int $saddleHeight,
        public ?int $frontSuspensionPressure,
        public ?int $rearSuspensionPressure,
        public ?string $pedalType,
        public ?string $notes,
    ) {}
}
