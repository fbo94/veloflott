<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBike;

final class BikeNotFoundException extends \Exception
{
    public function __construct(string $bikeId)
    {
        parent::__construct("Bike with ID {$bikeId} not found");
    }
}
