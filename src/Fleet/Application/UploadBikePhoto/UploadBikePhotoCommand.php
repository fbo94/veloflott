<?php

declare(strict_types=1);

namespace Fleet\Application\UploadBikePhoto;

use Illuminate\Http\UploadedFile;

final readonly class UploadBikePhotoCommand
{
    public function __construct(
        public string $bikeId,
        public UploadedFile $photo,
    ) {
    }
}
