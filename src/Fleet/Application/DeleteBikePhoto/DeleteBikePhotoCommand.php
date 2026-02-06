<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteBikePhoto;

final readonly class DeleteBikePhotoCommand
{
    public function __construct(
        public string $bikeId,
        public string $photoUrl,
    ) {}
}
