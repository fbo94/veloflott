<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Rental\Domain\EquipmentType;

final readonly class EquipmentItemData
{
    public function __construct(
        public EquipmentType $type,
        public int $quantity,
        public float $pricePerUnit,
    ) {
    }
}
