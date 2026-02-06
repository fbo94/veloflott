<?php

declare(strict_types=1);

namespace Fleet\Application\ChangeBikeStatus;

use Fleet\Application\UpdateBike\BikeNotFoundException;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatus;
use Fleet\Domain\BikeStatusHistory;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;
use Fleet\Domain\UnavailabilityReason;
use Illuminate\Support\Str;

final readonly class ChangeBikeStatusHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
        private BikeStatusHistoryRepositoryInterface $historyRepository,
    ) {}

    public function handle(ChangeBikeStatusCommand $command): ChangeBikeStatusResponse
    {
        $bike = $this->bikeRepository->findById($command->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($command->bikeId);
        }

        // Store old status for history
        $oldStatus = $bike->status();

        // Parse new status
        $newStatus = BikeStatus::from($command->status);

        // Parse unavailability reason if provided
        $unavailabilityReason = $command->unavailabilityReason !== null
            ? UnavailabilityReason::from($command->unavailabilityReason)
            : null;

        try {
            // Change bike status
            $bike->changeStatusWithReason(
                $newStatus,
                $unavailabilityReason,
                $command->unavailabilityComment
            );

            // Save bike
            $this->bikeRepository->save($bike);

            // Record status change in history
            $history = new BikeStatusHistory(
                id: Str::uuid()->toString(),
                bikeId: $bike->id(),
                oldStatus: $oldStatus,
                newStatus: $newStatus,
                unavailabilityReason: $unavailabilityReason,
                unavailabilityComment: $command->unavailabilityComment,
            );
            $this->historyRepository->save($history);

            return new ChangeBikeStatusResponse(id: $bike->id());
        } catch (\DomainException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }
}
