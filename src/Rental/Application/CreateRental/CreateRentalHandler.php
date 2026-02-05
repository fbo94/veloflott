<?php

declare(strict_types=1);

namespace Rental\Application\CreateRental;

use Customer\Domain\CustomerRepositoryInterface;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalEquipment;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;
use Illuminate\Support\Str;

final class CreateRentalHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly CustomerRepositoryInterface $customers,
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(CreateRentalCommand $command): CreateRentalResponse
    {
        // Vérifier que le client existe
        $customer = $this->customers->findById($command->customerId);
        if ($customer === null) {
            throw new CustomerNotFoundException($command->customerId);
        }

        // Calculer la date de retour prévue
        $expectedReturnDate = $this->calculateExpectedReturnDate(
            $command->startDate,
            $command->duration,
            $command->customEndDate
        );

        // Vérifier la disponibilité des vélos et créer les items
        $rentalItems = [];
        $rentalId = Str::uuid()->toString();

        foreach ($command->bikeItems as $bikeItemData) {
            $bike = $this->bikes->findById($bikeItemData->bikeId);
            if ($bike === null) {
                throw new BikeNotFoundException($bikeItemData->bikeId);
            }

            if (!$bike->isRentable()) {
                throw new BikeNotAvailableException($bikeItemData->bikeId, $bike->status());
            }

            $rentalItems[] = new RentalItem(
                id: Str::uuid()->toString(),
                rentalId: $rentalId,
                bikeId: $bikeItemData->bikeId,
                dailyRate: $bikeItemData->dailyRate,
                quantity: $bikeItemData->quantity,
            );
        }

        // Créer les équipements
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

        // Créer la location
        $rental = new Rental(
            id: $rentalId,
            customerId: $command->customerId,
            startDate: $command->startDate,
            expectedReturnDate: $expectedReturnDate,
            actualReturnDate: null,
            duration: $command->duration,
            depositAmount: $command->depositAmount,
            totalAmount: 0.0, // Sera recalculé
            discountAmount: 0.0,
            taxRate: 20.0, // TVA française par défaut
            taxAmount: 0.0, // Sera recalculé
            totalWithTax: 0.0, // Sera recalculé
            status: RentalStatus::PENDING,
            items: $rentalItems,
            equipments: $equipments,
            depositStatus: null,
            depositRetained: null,
            cancellationReason: null,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        // Recalculer le montant total
        $rental->recalculateTotalAmount();

        // Sauvegarder la location avec ses items et équipements
        $this->rentals->saveWithItems($rental);

        // Marquer les vélos comme "en location"
        foreach ($rentalItems as $item) {
            $bike = $this->bikes->findById($item->bikeId());
            $bike->markAsRented();
            $this->bikes->save($bike);
        }

        return CreateRentalResponse::fromRental($rental, $customer);
    }

    private function calculateExpectedReturnDate(
        \DateTimeImmutable $startDate,
        RentalDuration $duration,
        ?\DateTimeImmutable $customEndDate,
    ): \DateTimeImmutable {
        if ($duration === RentalDuration::CUSTOM) {
            if ($customEndDate === null) {
                throw new \DomainException('Custom duration requires a custom end date');
            }
            return $customEndDate;
        }

        $hours = $duration->hours();
        return $startDate->modify("+{$hours} hours");
    }
}
