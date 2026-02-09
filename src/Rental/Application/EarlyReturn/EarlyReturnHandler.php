<?php

declare(strict_types=1);

namespace Rental\Application\EarlyReturn;

use DateTimeImmutable;
use Fleet\Domain\BikeRepositoryInterface;
use Rental\Application\CheckOutRental\RentalNotFoundException;
use Rental\Application\Services\EarlyReturnCalculator;
use Rental\Domain\DepositStatus;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

final class EarlyReturnHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly BikeRepositoryInterface $bikes,
        private readonly EarlyReturnCalculator $earlyReturnCalculator,
    ) {
    }

    public function handle(EarlyReturnCommand $command): EarlyReturnResponse
    {
        // 1. Find the rental
        $rental = $this->rentals->findById($command->rentalId);
        if ($rental === null) {
            throw new RentalNotFoundException($command->rentalId);
        }

        // 2. Verify it can be early returned (must be ACTIVE)
        if (!$rental->status()->canEarlyReturn()) {
            throw new CannotEarlyReturnException(
                $command->rentalId,
                $rental->status(),
            );
        }

        // 3. Verify return date is before expected
        if ($command->actualReturnDate >= $rental->expectedReturnDate()) {
            throw new NotAnEarlyReturnException(
                $command->rentalId,
                $command->actualReturnDate,
                $rental->expectedReturnDate(),
            );
        }

        // 4. Calculate early return fees and refund
        $earlyReturnResult = $this->earlyReturnCalculator->calculate(
            startDate: $rental->startDate(),
            expectedReturnDate: $rental->expectedReturnDate(),
            actualReturnDate: $command->actualReturnDate,
            totalAmount: $rental->totalAmount(),
            tenantId: $command->tenantId,
            siteId: $command->siteId,
        );

        // 5. Process bike conditions and determine deposit status
        $depositRetained = $command->depositRetained ?? 0.0;
        $depositStatus = $depositRetained > 0
            ? ($depositRetained >= $rental->depositAmount() ? DepositStatus::RETAINED : DepositStatus::PARTIAL)
            : DepositStatus::RELEASED;

        // 6. Update rental items with return conditions
        foreach ($command->bikesCondition as $bikeCondition) {
            $rental->updateItemReturnCondition(
                bikeId: $bikeCondition->bikeId,
                condition: $bikeCondition->condition->value,
                damageDescription: $bikeCondition->damageDescription,
                damagePhotos: $bikeCondition->damagePhotos ?? [],
            );
        }

        // 7. Complete the rental
        $rental->complete(
            actualReturnDate: $command->actualReturnDate,
            depositStatus: $depositStatus,
            depositRetained: $depositRetained,
        );

        // 8. Save the rental
        $this->rentals->saveWithItems($rental);

        // 9. Release bikes (mark as AVAILABLE)
        foreach ($rental->items() as $item) {
            $bike = $this->bikes->findById($item->bikeId());
            if ($bike !== null) {
                $bike->markAsAvailable();
                $this->bikes->save($bike);
            }
        }

        return new EarlyReturnResponse(
            rentalId: $rental->id(),
            status: RentalStatus::COMPLETED->value,
            actualReturnDate: $command->actualReturnDate->format('Y-m-d H:i:s'),
            originalAmount: $rental->totalAmount(),
            unusedDays: $earlyReturnResult->unusedDays,
            unusedAmount: $earlyReturnResult->unusedAmount,
            earlyReturnFee: $earlyReturnResult->feeAmount,
            refundAmount: $earlyReturnResult->refundAmount,
            depositAmount: $rental->depositAmount(),
            depositRetained: $depositRetained,
            depositRefunded: $rental->depositAmount() - $depositRetained,
            depositStatus: $depositStatus->value,
            message: 'Early return processed successfully',
        );
    }
}
