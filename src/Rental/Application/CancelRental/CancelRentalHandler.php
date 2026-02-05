<?php

declare(strict_types=1);

namespace Rental\Application\CancelRental;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\RentalRepositoryInterface;

final class CancelRentalHandler
{
    public function __construct(
        private readonly RentalRepositoryInterface $rentals,
        private readonly BikeRepositoryInterface $bikes,
    ) {
    }

    public function handle(CancelRentalCommand $command): CancelRentalResponse
    {
        $rental = $this->rentals->findById($command->rentalId);

        if ($rental === null) {
            throw new RentalNotFoundException($command->rentalId);
        }

        // Annuler la location (le domaine vérifie le statut)
        try {
            $rental->cancel($command->cancellationReason);
        } catch (\DomainException $e) {
            throw new CannotCancelRentalException($e->getMessage());
        }

        // Sauvegarder
        $this->rentals->save($rental);

        // Remettre les vélos en disponible
        foreach ($rental->items() as $item) {
            $bike = $this->bikes->findById($item->bikeId());
            if ($bike !== null && $bike->status()->value === 'rented') {
                $bike->markAsAvailable();
                $this->bikes->save($bike);
            }
        }

        return new CancelRentalResponse(
            rentalId: $rental->id(),
            status: $rental->status()->value,
            cancellationReason: $rental->cancellationReason(),
            depositStatus: $rental->depositStatus()->value,
            message: 'Rental cancelled successfully. Deposit will be released.',
        );
    }
}
