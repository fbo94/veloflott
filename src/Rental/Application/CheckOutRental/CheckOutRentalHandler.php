<?php

declare(strict_types=1);

namespace Rental\Application\CheckOutRental;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Rental\Domain\RentalRepositoryInterface;

final class CheckOutRentalHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(CheckOutRentalCommand $command): CheckOutRentalResponse
    {
        // Récupérer la location
        $rental = $this->rentals->findById($command->rentalId);
        if ($rental === null) {
            throw new RentalNotFoundException($command->rentalId);
        }

        // Vérifier que la location peut être clôturée
        if (! $rental->status()->canCheckOut()) {
            throw new RentalCannotBeCheckedOutException($command->rentalId, $rental->status());
        }

        // Enregistrer les conditions de retour pour chaque vélo
        foreach ($command->bikesCondition as $bikeConditionData) {
            $item = $this->findRentalItemByBikeId($rental, $bikeConditionData->bikeId);
            if ($item === null) {
                throw new BikeNotInRentalException($bikeConditionData->bikeId, $command->rentalId);
            }

            $item->recordCheckOut(
                condition: $bikeConditionData->condition,
                damageDescription: $bikeConditionData->damageDescription,
                damagePhotos: $bikeConditionData->damagePhotos,
            );

            // Mettre à jour le statut du vélo
            $bike = $this->bikes->findById($bikeConditionData->bikeId);
            if ($bike !== null) {
                if ($bikeConditionData->condition->requiresMaintenance()) {
                    $bike->changeStatus(BikeStatus::MAINTENANCE);
                } else {
                    $bike->markAsReturned();
                }
                $this->bikes->save($bike);
            }
        }

        // Calculer les frais de retard
        $lateFee = 0.0;
        if ($rental->isLate()) {
            $lateFee = $rental->calculateLateFeeSupplement($command->hourlyLateRate);
        }

        // Effectuer le check-out
        $rental->checkOut(
            actualReturnDate: $command->actualReturnDate,
            lateFee: $lateFee,
            depositRetained: $command->depositRetained,
        );

        // Sauvegarder
        $this->rentals->saveWithItems($rental);

        return CheckOutRentalResponse::fromRental($rental, $lateFee);
    }

    private function findRentalItemByBikeId($rental, string $bikeId)
    {
        foreach ($rental->items() as $item) {
            if ($item->bikeId() === $bikeId) {
                return $item;
            }
        }

        return null;
    }
}
