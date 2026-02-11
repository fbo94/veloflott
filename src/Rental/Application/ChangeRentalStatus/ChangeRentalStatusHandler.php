<?php

declare(strict_types=1);

namespace Rental\Application\ChangeRentalStatus;

use Fleet\Domain\BikeRepositoryInterface;
use Rental\Domain\DepositStatus;
use Rental\Domain\Exceptions\RentalException;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;

final readonly class ChangeRentalStatusHandler
{
    public function __construct(
        private RentalRepositoryInterface $rentalRepository,
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(ChangeRentalStatusCommand $command): ChangeRentalStatusResponse
    {
        $rental = $this->rentalRepository->findById($command->rentalId);
        if ($rental === null) {
            throw RentalException::notFound($command->rentalId);
        }

        $previousStatus = $rental->status();

        if ($previousStatus === $command->newStatus) {
            return new ChangeRentalStatusResponse(
                rentalId: $rental->id(),
                previousStatus: $previousStatus->value,
                newStatus: $command->newStatus->value,
                message: 'Rental is already in the requested status.',
            );
        }

        $this->validateTransition($previousStatus, $command->newStatus);

        $this->applyTransition($rental, $command);

        $this->rentalRepository->save($rental);

        $this->handleBikeStatusChanges($rental, $previousStatus, $command->newStatus);

        return new ChangeRentalStatusResponse(
            rentalId: $rental->id(),
            previousStatus: $previousStatus->value,
            newStatus: $command->newStatus->value,
            message: $this->getSuccessMessage($command->newStatus),
        );
    }

    private function validateTransition(RentalStatus $from, RentalStatus $to): void
    {
        $validTransitions = [
            RentalStatus::RESERVED->value => [
                RentalStatus::PENDING->value,
                RentalStatus::ACTIVE->value,
                RentalStatus::CANCELLED->value,
            ],
            RentalStatus::PENDING->value => [
                RentalStatus::ACTIVE->value,
                RentalStatus::CANCELLED->value,
            ],
            RentalStatus::ACTIVE->value => [
                RentalStatus::COMPLETED->value,
            ],
        ];

        $allowedTargets = $validTransitions[$from->value] ?? [];

        if (!in_array($to->value, $allowedTargets, true)) {
            throw RentalException::invalidStatusTransition($from->value, $to->value);
        }
    }

    /**
     * @param \Rental\Domain\Rental $rental
     */
    private function applyTransition(mixed $rental, ChangeRentalStatusCommand $command): void
    {
        match ($command->newStatus) {
            RentalStatus::PENDING => $rental->confirm(),
            RentalStatus::ACTIVE => $this->startRental($rental),
            RentalStatus::COMPLETED => $rental->complete(
                new \DateTimeImmutable(),
                DepositStatus::RELEASED,
                0.0,
            ),
            RentalStatus::CANCELLED => $rental->cancel($command->reason ?? 'Status changed manually'),
            default => throw new \DomainException("Unsupported transition to {$command->newStatus->value}"),
        };
    }

    /**
     * @param \Rental\Domain\Rental $rental
     */
    private function startRental(mixed $rental): void
    {
        if ($rental->status() === RentalStatus::RESERVED) {
            $rental->confirm();
        }
        $rental->start();
    }

    /**
     * @param \Rental\Domain\Rental $rental
     */
    private function handleBikeStatusChanges(
        mixed $rental,
        RentalStatus $previousStatus,
        RentalStatus $newStatus,
    ): void {
        // When transitioning to ACTIVE, mark bikes as RENTED
        if ($newStatus === RentalStatus::ACTIVE && !$previousStatus->blocksBikePhysically()) {
            foreach ($rental->items() as $item) {
                $bike = $this->bikeRepository->findById($item->bikeId());
                if ($bike !== null) {
                    $bike->markAsRented();
                    $this->bikeRepository->save($bike);
                }
            }
        }

        // When transitioning from ACTIVE to COMPLETED/CANCELLED, release bikes
        if ($previousStatus === RentalStatus::ACTIVE && $newStatus->isFinal()) {
            foreach ($rental->items() as $item) {
                $bike = $this->bikeRepository->findById($item->bikeId());
                if ($bike !== null) {
                    $bike->markAsAvailable();
                    $this->bikeRepository->save($bike);
                }
            }
        }
    }

    private function getSuccessMessage(RentalStatus $newStatus): string
    {
        return match ($newStatus) {
            RentalStatus::PENDING => 'Rental confirmed. Customer is ready for check-in.',
            RentalStatus::ACTIVE => 'Rental started. Bikes are now rented.',
            RentalStatus::COMPLETED => 'Rental completed successfully.',
            RentalStatus::CANCELLED => 'Rental has been cancelled.',
            default => 'Rental status updated.',
        };
    }
}
