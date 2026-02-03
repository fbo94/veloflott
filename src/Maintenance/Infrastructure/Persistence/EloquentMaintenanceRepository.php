<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure\Persistence;

use DateTimeImmutable;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\Maintenance;
use Maintenance\Domain\MaintenancePriority;
use Maintenance\Domain\MaintenanceReason;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Maintenance\Domain\MaintenanceStatus;
use Maintenance\Domain\MaintenanceType;

final class EloquentMaintenanceRepository implements MaintenanceRepositoryInterface
{
    public function save(Maintenance $maintenance): void
    {
        MaintenanceEloquentModel::updateOrCreate(
            ['id' => $maintenance->id()],
            [
                'bike_id' => $maintenance->bikeId(),
                'type' => $maintenance->type()->value,
                'reason' => $maintenance->reason()->value,
                'priority' => $maintenance->priority()->value,
                'status' => $maintenance->status()->value,
                'description' => $maintenance->description(),
                'scheduled_at' => $maintenance->scheduledAt(),
                'started_at' => $maintenance->startedAt(),
                'completed_at' => $maintenance->completedAt(),
                'work_description' => $maintenance->workDescription(),
                'parts_replaced' => $maintenance->partsReplaced(),
                'cost' => $maintenance->cost(),
                'updated_at' => $maintenance->updatedAt(),
            ]
        );
    }

    /**
     * @throws MaintenanceException
     */
    public function findById(string $id): Maintenance
    {
        $model = MaintenanceEloquentModel::find($id);

        if (!$model) {
            throw MaintenanceException::notFound($id);
        }

        return $this->toDomain($model);
    }

    public function findByBikeId(string $bikeId): array
    {
        return MaintenanceEloquentModel::where('bike_id', $bikeId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function findInProgress(): array
    {
        return $this->findByStatus(MaintenanceStatus::IN_PROGRESS);
    }

    public function findByStatus(MaintenanceStatus $status): array
    {
        return MaintenanceEloquentModel::where('status', $status->value)
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function findWithFilters(
        ?string              $bikeId = null,
        ?MaintenanceStatus   $status = null,
        ?MaintenancePriority $priority = null,
        ?DateTimeImmutable   $dateFrom = null,
        ?DateTimeImmutable   $dateTo = null,
    ): array {
        $query = MaintenanceEloquentModel::query();

        if ($bikeId !== null) {
            $query->where('bike_id', $bikeId);
        }

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        if ($priority !== null) {
            $query->where('priority', $priority->value);
        }

        if ($dateFrom !== null) {
            $query->where('scheduled_at', '>=', $dateFrom->format('Y-m-d H:i:s'));
        }

        if ($dateTo !== null) {
            $query->where('scheduled_at', '<=', $dateTo->format('Y-m-d H:i:s'));
        }

        return $query
            ->orderBy('priority', 'desc')
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function countByStatus(): array
    {
        $counts = MaintenanceEloquentModel::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return $counts;
    }

    public function findScheduledOnDate(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->format('Y-m-d 00:00:00');
        $endOfDay = $date->format('Y-m-d 23:59:59');

        return MaintenanceEloquentModel::whereBetween('scheduled_at', [$startOfDay, $endOfDay])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function findCompletedOnDate(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->format('Y-m-d 00:00:00');
        $endOfDay = $date->format('Y-m-d 23:59:59');

        return MaintenanceEloquentModel::where('status', MaintenanceStatus::COMPLETED->value)
            ->whereBetween('completed_at', [$startOfDay, $endOfDay])
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function countUrgentPending(): int
    {
        return MaintenanceEloquentModel::where('priority', MaintenancePriority::URGENT->value)
            ->where('status', MaintenanceStatus::TODO->value)
            ->count();
    }

    public function delete(string $id): void
    {
        MaintenanceEloquentModel::where('id', $id)->delete();
    }

    private function toDomain(MaintenanceEloquentModel $model): Maintenance
    {
        return Maintenance::reconstitute(
            id: $model->id,
            bikeId: $model->bike_id,
            type: MaintenanceType::from($model->type),
            reason: MaintenanceReason::from($model->reason),
            priority: MaintenancePriority::from($model->priority),
            status: MaintenanceStatus::from($model->status),
            description: $model->description,
            scheduledAt: DateTimeImmutable::createFromInterface($model->scheduled_at),
            startedAt: $model->started_at ? DateTimeImmutable::createFromInterface($model->started_at) : null,
            completedAt: $model->completed_at ? DateTimeImmutable::createFromInterface($model->completed_at) : null,
            workDescription: $model->work_description,
            partsReplaced: $model->parts_replaced,
            cost: $model->cost,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }
}
