<?php

declare(strict_types=1);

namespace Rental\Application\EarlyReturn;

use DateTimeImmutable;
use Rental\Application\CheckOutRental\BikeConditionData;

final readonly class EarlyReturnCommand
{
    /**
     * @param BikeConditionData[] $bikesCondition
     */
    public function __construct(
        public string $rentalId,
        public DateTimeImmutable $actualReturnDate,
        public array $bikesCondition,
        public ?float $depositRetained = null,
        public ?string $tenantId = null,
        public ?string $siteId = null,
    ) {
    }
}
