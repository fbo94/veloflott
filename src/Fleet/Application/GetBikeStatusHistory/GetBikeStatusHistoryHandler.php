<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeStatusHistory;

use Fleet\Application\UpdateBike\BikeNotFoundException;
use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;

final readonly class GetBikeStatusHistoryHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
        private BikeStatusHistoryRepositoryInterface $historyRepository,
    ) {}

    public function handle(GetBikeStatusHistoryQuery $query): GetBikeStatusHistoryResponse
    {
        $bike = $this->bikeRepository->findById($query->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($query->bikeId);
        }

        $history = $this->historyRepository->findByBikeId($query->bikeId);

        $historyDtos = array_map(
            fn ($item) => new BikeStatusHistoryDto(
                id: $item->id(),
                oldStatus: $item->oldStatus()->value,
                oldStatusLabel: $item->oldStatus()->label(),
                newStatus: $item->newStatus()->value,
                newStatusLabel: $item->newStatus()->label(),
                unavailabilityReason: $item->unavailabilityReason()?->value,
                unavailabilityReasonLabel: $item->unavailabilityReason()?->label(),
                unavailabilityComment: $item->unavailabilityComment(),
                changedAt: $item->changedAt()->format('Y-m-d H:i:s'),
            ),
            $history
        );

        return new GetBikeStatusHistoryResponse($historyDtos);
    }
}
