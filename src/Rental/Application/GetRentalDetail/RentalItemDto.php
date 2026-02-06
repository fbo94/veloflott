<?php

declare(strict_types=1);

namespace Rental\Application\GetRentalDetail;

use Rental\Domain\RentalItem;

final readonly class RentalItemDto
{
    public function __construct(
        public string $id,
        public string $bikeId,
        public string $bikeBrand,
        public string $bikeModel,
        public string $bikeCategoryId,
        public string $bikeCategoryName,
        public string $bikeInternalNumber,
        public string $bikeSerialNumber,
        public ?float $bikePurchasePrice,
        public float $dailyRate,
        public int $quantity,
        public float $numberOfDays,
        public float $totalAmount,
        public ?int $clientHeight,
        public ?int $clientWeight,
        public ?int $saddleHeight,
        public ?int $frontSuspensionPressure,
        public ?int $rearSuspensionPressure,
        public ?string $pedalType,
        public ?string $checkInNotes,
        public ?string $returnCondition,
        public ?string $damageDescription,
        public array $damagePhotos,
    ) {}

    public static function fromRentalItem(RentalItem $item, array $bikeDetails, float $numberOfDays): self
    {
        $totalAmount = $item->dailyRate() * $numberOfDays * $item->quantity();

        return new self(
            id: $item->id(),
            bikeId: $item->bikeId(),
            bikeBrand: $bikeDetails['brand'],
            bikeModel: $bikeDetails['model'],
            bikeCategoryId: $bikeDetails['category_id'],
            bikeCategoryName: $bikeDetails['category_name'],
            bikeInternalNumber: $bikeDetails['internal_number'],
            bikeSerialNumber: $bikeDetails['serial_number'],
            bikePurchasePrice: $bikeDetails['purchase_price'],
            dailyRate: $item->dailyRate(),
            quantity: $item->quantity(),
            numberOfDays: $numberOfDays,
            totalAmount: $totalAmount,
            clientHeight: $item->clientHeight(),
            clientWeight: $item->clientWeight(),
            saddleHeight: $item->saddleHeight(),
            frontSuspensionPressure: $item->frontSuspensionPressure(),
            rearSuspensionPressure: $item->rearSuspensionPressure(),
            pedalType: $item->pedalType(),
            checkInNotes: $item->checkInNotes(),
            returnCondition: $item->returnCondition()?->value,
            damageDescription: $item->damageDescription(),
            damagePhotos: $item->damagePhotos() ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'bike_id' => $this->bikeId,
            'bike_brand' => $this->bikeBrand,
            'bike_model' => $this->bikeModel,
            'bike_category_id' => $this->bikeCategoryId,
            'bike_category_name' => $this->bikeCategoryName,
            'bike_internal_number' => $this->bikeInternalNumber,
            'bike_serial_number' => $this->bikeSerialNumber,
            'bike_purchase_price' => $this->bikePurchasePrice,
            'daily_rate' => $this->dailyRate,
            'quantity' => $this->quantity,
            'number_of_days' => $this->numberOfDays,
            'total_amount' => $this->totalAmount,
            'client_height' => $this->clientHeight,
            'client_weight' => $this->clientWeight,
            'saddle_height' => $this->saddleHeight,
            'front_suspension_pressure' => $this->frontSuspensionPressure,
            'rear_suspension_pressure' => $this->rearSuspensionPressure,
            'pedal_type' => $this->pedalType,
            'check_in_notes' => $this->checkInNotes,
            'return_condition' => $this->returnCondition,
            'damage_description' => $this->damageDescription,
            'damage_photos' => $this->damagePhotos,
        ];
    }
}
