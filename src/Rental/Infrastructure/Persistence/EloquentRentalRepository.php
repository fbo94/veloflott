<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence;

use Rental\Domain\BikeCondition;
use Rental\Domain\DepositStatus;
use Rental\Domain\EquipmentType;
use Rental\Domain\Rental;
use Rental\Domain\RentalDuration;
use Rental\Domain\RentalEquipment;
use Rental\Domain\RentalItem;
use Rental\Domain\RentalRepositoryInterface;
use Rental\Domain\RentalStatus;
use Rental\Infrastructure\Persistence\Models\RentalEloquentModel;
use Rental\Infrastructure\Persistence\Models\RentalEquipmentEloquentModel;
use Rental\Infrastructure\Persistence\Models\RentalItemEloquentModel;

final class EloquentRentalRepository implements RentalRepositoryInterface
{
    public function findById(string $id): ?Rental
    {
        $model = RentalEloquentModel::with(['items', 'equipments'])->find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return Rental[]
     */
    public function findActiveRentals(): array
    {
        return RentalEloquentModel::with(['items', 'equipments', 'customer'])
            ->where('status', RentalStatus::ACTIVE->value)
            ->orderBy('expected_return_date')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Rental[]
     */
    public function findByCustomerId(string $customerId): array
    {
        return RentalEloquentModel::with(['items', 'equipments'])
            ->where('customer_id', $customerId)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    /**
     * @return Rental[]
     */
    public function findLateRentals(): array
    {
        return RentalEloquentModel::with(['items', 'equipments', 'customer'])
            ->where('status', RentalStatus::ACTIVE->value)
            ->where('expected_return_date', '<', now())
            ->orderBy('expected_return_date')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function countActive(): int
    {
        return RentalEloquentModel::where('status', RentalStatus::ACTIVE->value)->count();
    }

    public function findByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return RentalEloquentModel::with(['items', 'equipments'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')])
                    ->orWhereBetween('expected_return_date', [$from->format('Y-m-d H:i:s'), $to->format('Y-m-d H:i:s')]);
            })
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function countByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return RentalEloquentModel::whereBetween('start_date', [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s')
        ])->count();
    }

    public function sumRevenueByPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): int
    {
        return (int) RentalEloquentModel::whereBetween('start_date', [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s')
        ])
            ->where('status', '!=', RentalStatus::CANCELLED->value)
            ->sum('total_amount');
    }

    public function getAverageRentalDurationHours(): float
    {
        $avgSeconds = RentalEloquentModel::query()
            ->where('status', RentalStatus::COMPLETED->value)
            ->whereNotNull('actual_return_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, start_date, actual_return_date)) as avg_duration')
            ->value('avg_duration');

        if ($avgSeconds === null) {
            return 0.0;
        }

        return round($avgSeconds / 3600, 1);
    }

    public function findStartedOnDate(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->format('Y-m-d 00:00:00');
        $endOfDay = $date->format('Y-m-d 23:59:59');

        return RentalEloquentModel::with(['items', 'equipments', 'customer'])
            ->whereBetween('start_date', [$startOfDay, $endOfDay])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function findExpectedReturnOnDate(\DateTimeImmutable $date): array
    {
        $startOfDay = $date->format('Y-m-d 00:00:00');
        $endOfDay = $date->format('Y-m-d 23:59:59');

        return RentalEloquentModel::with(['items', 'equipments', 'customer'])
            ->whereBetween('expected_return_date', [$startOfDay, $endOfDay])
            ->orderBy('expected_return_date')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function getStatsByBike(?int $limit = null): array
    {
        $query = RentalItemEloquentModel::query()
            ->selectRaw('bike_id, COUNT(*) as rental_count, SUM(daily_rate * quantity) as total_revenue')
            ->groupBy('bike_id')
            ->orderBy('rental_count', 'desc');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($item) {
            return [
                'bike_id' => $item->bike_id,
                'rental_count' => $item->rental_count,
                'total_revenue' => (int) $item->total_revenue,
            ];
        })->all();
    }

    public function findByBikeId(string $bikeId, ?array $statuses = null): array
    {
        $query = RentalEloquentModel::query()
            ->with(['items', 'equipments'])
            ->whereHas('items', function ($query) use ($bikeId) {
                $query->where('bike_id', $bikeId);
            });

        if ($statuses !== null && !empty($statuses)) {
            $statusValues = array_map(fn (RentalStatus $status) => $status->value, $statuses);
            $query->whereIn('status', $statusValues);
        }

        return $query
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($model) => $this->toDomain($model))
            ->all();
    }

    public function save(Rental $rental): void
    {
        RentalEloquentModel::updateOrCreate(
            ['id' => $rental->id()],
            [
                'customer_id' => $rental->customerId(),
                'start_date' => $rental->startDate(),
                'expected_return_date' => $rental->expectedReturnDate(),
                'actual_return_date' => $rental->actualReturnDate(),
                'duration' => $rental->duration()->value,
                'deposit_amount' => $rental->depositAmount(),
                'total_amount' => $rental->totalAmount(),
                'discount_amount' => $rental->discountAmount(),
                'tax_rate' => $rental->taxRate(),
                'tax_amount' => $rental->taxAmount(),
                'total_with_tax' => $rental->totalWithTax(),
                'status' => $rental->status()->value,
                'deposit_status' => $rental->depositStatus()->value,
                'deposit_retained' => $rental->depositRetained(),
                'cancellation_reason' => $rental->cancellationReason(),
            ]
        );
    }

    public function saveWithItems(Rental $rental): void
    {
        // Sauvegarder la location
        $this->save($rental);

        // Supprimer les anciens items et équipements
        RentalItemEloquentModel::where('rental_id', $rental->id())->delete();
        RentalEquipmentEloquentModel::where('rental_id', $rental->id())->delete();

        // Sauvegarder les items
        foreach ($rental->items() as $item) {
            RentalItemEloquentModel::create([
                'id' => $item->id(),
                'rental_id' => $item->rentalId(),
                'bike_id' => $item->bikeId(),
                'daily_rate' => $item->dailyRate(),
                'quantity' => $item->quantity(),
                'client_height' => $item->clientHeight(),
                'client_weight' => $item->clientWeight(),
                'saddle_height' => $item->saddleHeight(),
                'front_suspension_pressure' => $item->frontSuspensionPressure(),
                'rear_suspension_pressure' => $item->rearSuspensionPressure(),
                'pedal_type' => $item->pedalType(),
                'check_in_notes' => $item->checkInNotes(),
                'return_condition' => $item->returnCondition()?->value,
                'damage_description' => $item->damageDescription(),
                'damage_photos' => $item->damagePhotos(),
            ]);
        }

        // Sauvegarder les équipements
        foreach ($rental->equipments() as $equipment) {
            RentalEquipmentEloquentModel::create([
                'id' => $equipment->id(),
                'rental_id' => $equipment->rentalId(),
                'type' => $equipment->type()->value,
                'quantity' => $equipment->quantity(),
                'price_per_unit' => $equipment->pricePerUnit(),
            ]);
        }
    }

    private function toDomain(RentalEloquentModel $model): Rental
    {
        $items = $model->items->map(fn ($itemModel) => new RentalItem(
            id: $itemModel->id,
            rentalId: $itemModel->rental_id,
            bikeId: $itemModel->bike_id,
            dailyRate: $itemModel->daily_rate,
            quantity: $itemModel->quantity,
            clientHeight: $itemModel->client_height,
            clientWeight: $itemModel->client_weight,
            saddleHeight: $itemModel->saddle_height,
            frontSuspensionPressure: $itemModel->front_suspension_pressure,
            rearSuspensionPressure: $itemModel->rear_suspension_pressure,
            pedalType: $itemModel->pedal_type,
            checkInNotes: $itemModel->check_in_notes,
            returnCondition: $itemModel->return_condition !== null
                ? BikeCondition::from($itemModel->return_condition)
                : null,
            damageDescription: $itemModel->damage_description,
            damagePhotos: $itemModel->damage_photos,
        ))->all();

        $equipments = $model->equipments->map(fn ($equipmentModel) => new RentalEquipment(
            id: $equipmentModel->id,
            rentalId: $equipmentModel->rental_id,
            type: EquipmentType::from($equipmentModel->type),
            quantity: $equipmentModel->quantity,
            pricePerUnit: $equipmentModel->price_per_unit,
        ))->all();

        return new Rental(
            id: $model->id,
            customerId: $model->customer_id,
            startDate: \DateTimeImmutable::createFromInterface($model->start_date),
            expectedReturnDate: \DateTimeImmutable::createFromInterface($model->expected_return_date),
            actualReturnDate: $model->actual_return_date !== null
                ? \DateTimeImmutable::createFromInterface($model->actual_return_date)
                : null,
            duration: RentalDuration::from($model->duration),
            depositAmount: $model->deposit_amount,
            totalAmount: $model->total_amount,
            discountAmount: $model->discount_amount ?? 0.0,
            taxRate: $model->tax_rate ?? 20.0,
            taxAmount: $model->tax_amount ?? 0.0,
            totalWithTax: $model->total_with_tax ?? 0.0,
            status: RentalStatus::from($model->status),
            items: $items,
            equipments: $equipments,
            depositStatus: DepositStatus::from($model->deposit_status),
            depositRetained: $model->deposit_retained,
            cancellationReason: $model->cancellation_reason,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: \DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }
}
