<?php

declare(strict_types=1);

namespace Rental\Domain;

/**
 * Entité représentant un équipement loué.
 */
final class RentalEquipment
{
    public function __construct(
        private readonly string $id,
        private readonly string $rentalId,
        private readonly EquipmentType $type,
        private readonly int $quantity,
        private readonly float $pricePerUnit,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function rentalId(): string
    {
        return $this->rentalId;
    }

    public function type(): EquipmentType
    {
        return $this->type;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function pricePerUnit(): float
    {
        return $this->pricePerUnit;
    }

    public function calculateAmount(): float
    {
        return $this->pricePerUnit * $this->quantity;
    }
}
