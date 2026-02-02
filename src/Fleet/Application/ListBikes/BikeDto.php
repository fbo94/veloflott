<?php

declare(strict_types=1);

namespace Fleet\Application\ListBikes;

use Fleet\Domain\Bike;
use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;

final readonly class BikeDto
{
    public function __construct(
        public string $id,
        public string $qrCodeUuid,
        public string $internalNumber,
        public string $modelId,
        public string $brand,
        public string $model,
        public string $status,
        public string $categoryId,
        public array $frameSize,
        public ?int $year,
        public ?string $color,
    ) {}

    public static function fromEloquentModel(BikeEloquentModel $bikeModel): self
    {
        return new self(
            id: $bikeModel->id,
            qrCodeUuid: $bikeModel->qr_code_uuid,
            internalNumber: $bikeModel->internal_number,
            modelId: $bikeModel->model_id,
            brand: $bikeModel->model->brand->name,
            model: $bikeModel->model->name,
            status: $bikeModel->status,
            categoryId: $bikeModel->category_id,
            frameSize: [
                'unit' => $bikeModel->frame_size_unit,
                'numeric_value' => $bikeModel->frame_size_numeric,
                'letter_value' => $bikeModel->frame_size_letter,
                'letter_equivalent' => $bikeModel->frame_size_letter_equivalent,
            ],
            year: $bikeModel->year,
            color: $bikeModel->color,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'qr_code_uuid' => $this->qrCodeUuid,
            'internal_number' => $this->internalNumber,
            'model_id' => $this->modelId,
            'brand' => $this->brand,
            'model' => $this->model,
            'status' => $this->status,
            'category_id' => $this->categoryId,
            'frame_size' => $this->frameSize,
            'year' => $this->year,
            'color' => $this->color,
        ];
    }
}
