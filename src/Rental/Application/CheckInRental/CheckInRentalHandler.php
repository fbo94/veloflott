<?php

declare(strict_types=1);

namespace Rental\Application\CheckInRental;

use Rental\Domain\Exceptions\RentalException;
use Rental\Domain\RentalRepositoryInterface;

/**
 * Gestionnaire pour effectuer le check-in d'une location.
 */
final readonly class CheckInRentalHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
    ) {
    }

    public function handle(CheckInRentalCommand $command): CheckInRentalResponse
    {
        // 1. Récupérer la location
        $rental = $this->rentalRepository->findById($command->rentalId);
        if ($rental === null) {
            throw RentalException::notFound($command->rentalId);
        }

        // 2. Vérifier que la location est en statut PENDING (check-in avant le départ)
        if (! $rental->status()->canStart()) {
            throw RentalException::cannotCheckIn(
                $command->rentalId,
                "Rental status is {$rental->status()->value}, cannot perform check-in"
            );
        }

        // 3. Enregistrer les données de check-in pour chaque vélo
        foreach ($command->bikesCheckIn as $bikeCheckIn) {
            // Trouver le RentalItem correspondant au vélo
            $item = null;
            foreach ($rental->items() as $rentalItem) {
                if ($rentalItem->bikeId() === $bikeCheckIn->bikeId) {
                    $item = $rentalItem;
                    break;
                }
            }

            if ($item === null) {
                throw RentalException::bikeNotInRental($command->rentalId, $bikeCheckIn->bikeId);
            }

            // Enregistrer le check-in
            $item->recordCheckIn(
                clientHeight: $bikeCheckIn->clientHeight,
                clientWeight: $bikeCheckIn->clientWeight,
                saddleHeight: $bikeCheckIn->saddleHeight,
                frontSuspensionPressure: $bikeCheckIn->frontSuspensionPressure,
                rearSuspensionPressure: $bikeCheckIn->rearSuspensionPressure,
                pedalType: $bikeCheckIn->pedalType,
                notes: $bikeCheckIn->notes,
            );
        }

        // 4. Démarrer la location (passe en statut ACTIVE)
        $rental->start();

        // 5. Sauvegarder
        $this->rentalRepository->saveWithItems($rental);

        return new CheckInRentalResponse(
            rentalId: $rental->id(),
            message: 'Check-in completed successfully. Rental is now active.',
        );
    }
}
