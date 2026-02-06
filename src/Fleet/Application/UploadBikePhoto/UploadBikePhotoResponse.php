<?php

declare(strict_types=1);

namespace Fleet\Application\UploadBikePhoto;

final readonly class UploadBikePhotoResponse
{
    public function __construct(
        public string $bikeId,
        public string $photoUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'bike_id' => $this->bikeId,
            'photo_url' => $this->photoUrl,
        ];
    }
}
