<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence;

use Fleet\Domain\BikeStatus;
use Fleet\Domain\BikeStatusHistory;
use Fleet\Domain\BikeStatusHistoryRepositoryInterface;
use Fleet\Domain\UnavailabilityReason;
use Fleet\Infrastructure\Persistence\Models\BikeStatusHistoryEloquentModel;

final class EloquentBikeStatusHistoryRepository implements BikeStatusHistoryRepositoryInterface
{
    public function save(BikeStatusHistory $history): void
    {
        BikeStatusHistoryEloquentModel::create([
            'id' => $history->id(),
            'bike_id' => $history->bikeId(),
            'old_status' => $history->oldStatus()->value,
            'new_status' => $history->newStatus()->value,
            'unavailability_reason' => $history->unavailabilityReason()?->value,
            'unavailability_comment' => $history->unavailabilityComment(),
            'changed_at' => $history->changedAt(),
        ]);
    }

    /**
     * @return BikeStatusHistory[]
     */
    public function findByBikeId(string $bikeId): array
    {
        return BikeStatusHistoryEloquentModel::where('bike_id', $bikeId)
            ->orderBy('changed_at', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    private function toDomain(BikeStatusHistoryEloquentModel $model): BikeStatusHistory
    {
        return new BikeStatusHistory(
            id: $model->id,
            bikeId: $model->bike_id,
            oldStatus: BikeStatus::from($model->old_status),
            newStatus: BikeStatus::from($model->new_status),
            unavailabilityReason: $model->unavailability_reason !== null
                ? UnavailabilityReason::from($model->unavailability_reason)
                : null,
            unavailabilityComment: $model->unavailability_comment,
            changedAt: \DateTimeImmutable::createFromInterface($model->changed_at),
        );
    }
}
