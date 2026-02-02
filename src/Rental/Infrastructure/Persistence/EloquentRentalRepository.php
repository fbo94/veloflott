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
            startDate: new \DateTimeImmutable($model->start_date),
            expectedReturnDate: new \DateTimeImmutable($model->expected_return_date),
            actualReturnDate: $model->actual_return_date !== null
                ? new \DateTimeImmutable($model->actual_return_date)
                : null,
            duration: RentalDuration::from($model->duration),
            depositAmount: $model->deposit_amount,
            totalAmount: $model->total_amount,
            status: RentalStatus::from($model->status),
            items: $items,
            equipments: $equipments,
            depositStatus: DepositStatus::from($model->deposit_status),
            depositRetained: $model->deposit_retained,
            cancellationReason: $model->cancellation_reason,
            createdAt: new \DateTimeImmutable($model->created_at),
            updatedAt: new \DateTimeImmutable($model->updated_at),
        );
    }
}
