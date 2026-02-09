<?php

declare(strict_types=1);

namespace Rental\Application\GetRentalDetail;

use Rental\Domain\RentalEquipment;

final readonly class RentalEquipmentDto
{
    public function __construct(
        public string $id,
        public string $type,
        public int $quantity,
        public float $pricePerUnit,
        public float $totalAmount,
    ) {
    }

    public static function fromRentalEquipment(RentalEquipment $equipment): self
    {
        return new self(
            id: $equipment->id(),
            type: $equipment->type()->value,
            quantity: $equipment->quantity(),
            pricePerUnit: $equipment->pricePerUnit(),
            totalAmount: $equipment->pricePerUnit() * $equipment->quantity(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'price_per_unit' => $this->pricePerUnit,
            'total_amount' => $this->totalAmount,
        ];
    }
}
