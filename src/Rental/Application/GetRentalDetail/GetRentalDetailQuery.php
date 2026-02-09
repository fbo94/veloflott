<?php

declare(strict_types=1);

namespace Rental\Application\GetRentalDetail;

final readonly class GetRentalDetailQuery
{
    public function __construct(
        public string $rentalId,
    ) {
    }
}
