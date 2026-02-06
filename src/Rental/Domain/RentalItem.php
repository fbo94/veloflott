<?php

declare(strict_types=1);

namespace Rental\Domain;

/**
 * Entité représentant un vélo loué dans une location.
 */
final class RentalItem
{
    public function __construct(
        private readonly string $id,
        private readonly string $rentalId,
        private readonly string $bikeId,
        private float $dailyRate,
        private int $quantity, // Généralement 1, mais peut être > 1 si même vélo
        // Check-in data
        private ?int $clientHeight = null,
        private ?int $clientWeight = null,
        private ?int $saddleHeight = null,
        private ?int $frontSuspensionPressure = null,
        private ?int $rearSuspensionPressure = null,
        private ?string $pedalType = null,
        private ?string $checkInNotes = null,
        // Check-out data
        private ?BikeCondition $returnCondition = null,
        private ?string $damageDescription = null,
        private ?array $damagePhotos = null,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function rentalId(): string
    {
        return $this->rentalId;
    }

    public function bikeId(): string
    {
        return $this->bikeId;
    }

    public function dailyRate(): float
    {
        return $this->dailyRate;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function clientHeight(): ?int
    {
        return $this->clientHeight;
    }

    public function clientWeight(): ?int
    {
        return $this->clientWeight;
    }

    public function saddleHeight(): ?int
    {
        return $this->saddleHeight;
    }

    public function frontSuspensionPressure(): ?int
    {
        return $this->frontSuspensionPressure;
    }

    public function rearSuspensionPressure(): ?int
    {
        return $this->rearSuspensionPressure;
    }

    public function pedalType(): ?string
    {
        return $this->pedalType;
    }

    public function checkInNotes(): ?string
    {
        return $this->checkInNotes;
    }

    public function returnCondition(): ?BikeCondition
    {
        return $this->returnCondition;
    }

    public function damageDescription(): ?string
    {
        return $this->damageDescription;
    }

    public function damagePhotos(): ?array
    {
        return $this->damagePhotos;
    }

    public function recordCheckIn(
        int $clientHeight,
        int $clientWeight,
        int $saddleHeight,
        ?int $frontSuspensionPressure,
        ?int $rearSuspensionPressure,
        ?string $pedalType,
        ?string $notes,
    ): void {
        $this->clientHeight = $clientHeight;
        $this->clientWeight = $clientWeight;
        $this->saddleHeight = $saddleHeight;
        $this->frontSuspensionPressure = $frontSuspensionPressure;
        $this->rearSuspensionPressure = $rearSuspensionPressure;
        $this->pedalType = $pedalType;
        $this->checkInNotes = $notes;
    }

    public function recordCheckOut(
        BikeCondition $condition,
        ?string $damageDescription = null,
        ?array $damagePhotos = null,
    ): void {
        $this->returnCondition = $condition;
        $this->damageDescription = $damageDescription;
        $this->damagePhotos = $damagePhotos;
    }

    public function calculateAmount(float $days): float
    {
        return $this->dailyRate * $days * $this->quantity;
    }
}
