<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Fleet\Domain\Bike;
use Fleet\Domain\Brand;
use Fleet\Domain\Model;

final readonly class CreateBikeResponse
{
    public function __construct(
        public string $id,
        public string $qrCodeUuid,
        public string $internalNumber,
        public string $modelId,
        public string $brandName,
        public string $modelName,
        public string $status,
        public array $frameSize,
    ) {
    }

    public static function fromBike(Bike $bike, Model $model, Brand $brand): self
    {
        return new self(
            id: $bike->id(),
            qrCodeUuid: $bike->qrCodeUuid(),
            internalNumber: $bike->internalNumber(),
            modelId: $bike->modelId(),
            brandName: $brand->name(),
            modelName: $model->name(),
            status: $bike->status()->value,
            frameSize: $bike->frameSize()->toArray(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'qr_code_uuid' => $this->qrCodeUuid,
            'internal_number' => $this->internalNumber,
            'model_id' => $this->modelId,
            'brand' => $this->brandName,
            'model' => $this->modelName,
            'status' => $this->status,
            'frame_size' => $this->frameSize,
        ];
    }
}
