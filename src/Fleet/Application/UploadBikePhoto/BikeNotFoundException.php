<?php

declare(strict_types=1);

namespace Fleet\Application\UploadBikePhoto;

final class BikeNotFoundException extends \Exception
{
    public function __construct(string $bikeId)
    {
        parent::__construct("Bike with ID {$bikeId} not found");
    }
}
