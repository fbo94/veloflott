<?php

declare(strict_types=1);

namespace Rental\Application\CheckInRental;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Application\Services\BikeAvailabilityServiceInterface;
use Rental\Domain\Exceptions\RentalException;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

/**
 * Gestionnaire pour effectuer le check-in d'une location.
 * Le check-in peut être effectué depuis RESERVED ou PENDING.
 * C'est à ce moment que les vélos passent physiquement en RENTED.
 */
final readonly class CheckInRentalHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
        private BikeRepositoryInterface $bikeRepository,
        private BikeAvailabilityServiceInterface $availabilityService,
    ) {
    }

    public function handle(CheckInRentalCommand $command): CheckInRentalResponse
    {
        // 1. Récupérer la location
        $rental = $this->rentalRepository->findById($command->rentalId);
        if ($rental === null) {
            throw RentalException::notFound($command->rentalId);
        }

        // 2. Vérifier que la location peut être démarrée
        // Accepte RESERVED (réservation future, client arrive) ou PENDING (location immédiate)
        $canStart = $rental->status()->canStart() || $rental->status()->canConfirm();
        if (!$canStart) {
            throw RentalException::cannotCheckIn(
                $command->rentalId,
                "Rental status is {$rental->status()->value}, cannot perform check-in",
            );
        }

        // 3. Vérifier que tous les vélos sont physiquement disponibles
        foreach ($rental->items() as $item) {
            if (!$this->availabilityService->isPhysicallyAvailable($item->bikeId())) {
                $bike = $this->bikeRepository->findById($item->bikeId());
                $statusValue = $bike?->status()->value ?? 'unknown';

                throw RentalException::cannotCheckIn(
                    $command->rentalId,
                    "Bike {$item->bikeId()} is not physically available (status: {$statusValue})",
                );
            }
        }

        // 4. Enregistrer les données de check-in pour chaque vélo
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

        // 5. Si la location était RESERVED, la passer d'abord en PENDING puis en ACTIVE
        // Si elle était déjà PENDING, juste la passer en ACTIVE
        if ($rental->status() === RentalStatus::RESERVED) {
            $rental->confirm(); // RESERVED → PENDING
        }

        // 6. Démarrer la location (PENDING → ACTIVE)
        $rental->start();

        // 7. Sauvegarder la location
        $this->rentalRepository->saveWithItems($rental);

        // 8. Marquer les vélos comme RENTED (blocage physique)
        foreach ($rental->items() as $item) {
            $bike = $this->bikeRepository->findById($item->bikeId());
            if ($bike !== null) {
                $bike->markAsRented();
                $this->bikeRepository->save($bike);
            }
        }

        return new CheckInRentalResponse(
            rentalId: $rental->id(),
            message: 'Check-in completed successfully. Rental is now active.',
        );
    }
}
