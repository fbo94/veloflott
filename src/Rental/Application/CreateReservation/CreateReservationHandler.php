<?php

declare(strict_types=1);

namespace Rental\Application\CreateReservation;

use Customer\Domain\CustomerRepositoryInterface;
use DateTimeImmutable;
use Fleet\Domain\BikeRepositoryInterface;
use Illuminate\Support\Str;
use Rental\Application\CreateRental\BikeNotFoundException;
use Rental\Application\CreateRental\CustomerNotFoundException;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalEquipment;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;
use Rental\Domain\Repository\RentalSettingsRepositoryInterface;

final class CreateReservationHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly CustomerRepositoryInterface $customers,
        private readonly BikeRepositoryInterface $bikes,
        private readonly BikeAvailabilityServiceInterface $availabilityService,
        private readonly RentalSettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function handle(CreateReservationCommand $command): CreateReservationResponse
    {
        // 1. Verify customer exists
        $customer = $this->customers->findById($command->customerId);
        if ($customer === null) {
            throw new CustomerNotFoundException($command->customerId);
        }

        // 2. Calculate expected return date
        $expectedReturnDate = $this->calculateExpectedReturnDate(
            $command->startDate,
            $command->duration,
            $command->customEndDate,
        );

        // 3. Validate reservation constraints
        $this->validateReservationConstraints(
            $command->startDate,
            $expectedReturnDate,
            $command->tenantId,
            $command->siteId,
        );

        // 4. Verify bike availability for the period (NOT physical availability)
        $rentalItems = [];
        $rentalId = Str::uuid()->toString();

        foreach ($command->bikeItems as $bikeItemData) {
            $bike = $this->bikes->findById($bikeItemData->bikeId);
            if ($bike === null) {
                throw new BikeNotFoundException($bikeItemData->bikeId);
            }

            // Check calendar availability (not physical status)
            $availability = $this->availabilityService->isAvailableForPeriod(
                $bikeItemData->bikeId,
                $command->startDate,
                $expectedReturnDate,
            );

            if (!$availability->isAvailable) {
                throw new BikeNotAvailableForPeriodException(
                    $bikeItemData->bikeId,
                    $command->startDate,
                    $expectedReturnDate,
                    $availability->reason ?? 'Bike not available for this period',
                );
            }

            $rentalItems[] = new RentalItem(
                id: Str::uuid()->toString(),
                rentalId: $rentalId,
                bikeId: $bikeItemData->bikeId,
                dailyRate: $bikeItemData->dailyRate,
                quantity: $bikeItemData->quantity,
            );
        }

        // 5. Create equipments
        $equipments = [];
        foreach ($command->equipmentItems as $equipmentData) {
            $equipments[] = new RentalEquipment(
                id: Str::uuid()->toString(),
                rentalId: $rentalId,
                type: $equipmentData->type,
                quantity: $equipmentData->quantity,
                pricePerUnit: $equipmentData->pricePerUnit,
            );
        }

        // 6. Determine initial status: RESERVED for future, PENDING for today
        $now = new DateTimeImmutable();
        $isToday = $command->startDate->format('Y-m-d') === $now->format('Y-m-d');
        $status = $isToday ? RentalStatus::PENDING : RentalStatus::RESERVED;

        // 7. Create the rental
        $rental = new Rental(
            id: $rentalId,
            customerId: $command->customerId,
            startDate: $command->startDate,
            expectedReturnDate: $expectedReturnDate,
            actualReturnDate: null,
            duration: $command->duration,
            depositAmount: $command->depositAmount,
            totalAmount: 0.0,
            discountAmount: 0.0,
            taxRate: 20.0,
            taxAmount: 0.0,
            totalWithTax: 0.0,
            status: $status,
            items: $rentalItems,
            equipments: $equipments,
            depositStatus: null, // Will be set to HELD by constructor
            depositRetained: null,
            cancellationReason: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        // 8. Recalculate total amount
        $rental->recalculateTotalAmount();

        // 9. Save the rental (NO bike status change here!)
        // Bikes remain AVAILABLE, only calendar dates are blocked
        $this->rentals->saveWithItems($rental);

        return CreateReservationResponse::fromRental($rental, $customer, $status);
    }

    private function calculateExpectedReturnDate(
        DateTimeImmutable $startDate,
        RentalDuration $duration,
        ?DateTimeImmutable $customEndDate,
    ): DateTimeImmutable {
        if ($duration === RentalDuration::CUSTOM) {
            if ($customEndDate === null) {
                throw new \DomainException('Custom duration requires a custom end date');
            }

            return $customEndDate;
        }

        $hours = $duration->hours();

        return $startDate->modify("+{$hours} hours");
    }

    private function validateReservationConstraints(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $tenantId,
        ?string $siteId,
    ): void {
        $settings = $this->settingsRepository->getEffectiveSettings($tenantId, $siteId);
        $now = new DateTimeImmutable();

        // Check minimum advance time
        $minStartDate = $now->modify("+{$settings->minReservationHoursAhead()} hours");
        if ($startDate < $now && $startDate->format('Y-m-d') !== $now->format('Y-m-d')) {
            throw new \DomainException('Reservation start date cannot be in the past');
        }

        // Check maximum duration
        $durationDays = (int) $startDate->diff($endDate)->days;
        if ($durationDays > $settings->maxRentalDurationDays()) {
            throw new \DomainException(
                "Rental duration cannot exceed {$settings->maxRentalDurationDays()} days",
            );
        }
    }
}
