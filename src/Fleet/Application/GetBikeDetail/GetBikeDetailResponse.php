<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeDetail;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

final readonly class GetBikeDetailResponse
{
    public function __construct(
        public string $id,
        public string $qrCodeUuid,
        public string $internalNumber,
        public string $modelId,
        public string $brand,
        public string $brandId,
        public string $model,
        public string $status,
        public string $categoryId,
        public string $category,
        public array $frameSize,
        public ?int $year,
        public ?string $serialNumber,
        public ?string $color,
        public ?string $wheelSize,
        public ?int $frontSuspension,
        public ?int $rearSuspension,
        public ?string $brakeType,
        public ?float $purchasePrice,
        public ?string $purchaseDate,
        public ?string $notes,
        public array $photos,
        public ?string $retirementReason,
        public ?string $retirementComment,
        public ?string $retiredAt,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEloquentModel(BikeEloquentModel $bikeModel): self
    {
        return new self(
            id: $bikeModel->id,
            qrCodeUuid: $bikeModel->qr_code_uuid,
            internalNumber: $bikeModel->internal_number,
            modelId: $bikeModel->model_id,
            brand: $bikeModel->model->brand->name,
            brandId: $bikeModel->model->brand->id,
            model: $bikeModel->model->name,
            status: $bikeModel->status,
            categoryId: $bikeModel->category_id,
            category: $bikeModel->category->name,
            frameSize: [
                'unit' => $bikeModel->frame_size_unit,
                'numeric_value' => $bikeModel->frame_size_numeric,
                'letter_value' => $bikeModel->frame_size_letter,
                'letter_equivalent' => $bikeModel->frame_size_letter_equivalent,
            ],
            year: $bikeModel->year,
            serialNumber: $bikeModel->serial_number,
            color: $bikeModel->color,
            wheelSize: $bikeModel->wheel_size,
            frontSuspension: $bikeModel->front_suspension,
            rearSuspension: $bikeModel->rear_suspension,
            brakeType: $bikeModel->brake_type,
            purchasePrice: $bikeModel->purchase_price,
            purchaseDate: $bikeModel->purchase_date?->format('Y-m-d'),
            notes: $bikeModel->notes,
            photos: $bikeModel->photos ?? [],
            retirementReason: $bikeModel->retirement_reason,
            retirementComment: $bikeModel->retirement_comment,
            retiredAt: $bikeModel->retired_at?->format('Y-m-d H:i:s'),
            createdAt: $bikeModel->created_at->format('Y-m-d H:i:s'),
            updatedAt: $bikeModel->updated_at->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'qr_code_uuid' => $this->qrCodeUuid,
            'internal_number' => $this->internalNumber,
            'model_id' => $this->modelId,
            'brand' => [
                'id' => $this->brandId,
                'name' => $this->brand,
            ],
            'model' => [
                'id' => $this->modelId,
                'name' => $this->model,
            ],
            'status' => $this->status,
            'category' => [
                'id' => $this->categoryId,
                'name' => $this->category,
            ],
            'frame_size' => $this->frameSize,
            'year' => $this->year,
            'serial_number' => $this->serialNumber,
            'color' => $this->color,
            'wheel_size' => $this->wheelSize,
            'front_suspension' => $this->frontSuspension,
            'rear_suspension' => $this->rearSuspension,
            'brake_type' => $this->brakeType,
            'purchase_price' => $this->purchasePrice,
            'purchase_date' => $this->purchaseDate,
            'notes' => $this->notes,
            'photos' => $this->photos,
            'retirement_reason' => $this->retirementReason,
            'retirement_comment' => $this->retirementComment,
            'retired_at' => $this->retiredAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
