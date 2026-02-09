<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBike;

final readonly class UpdateBikeResponse
{
    public function __construct(
        public string $id,
        public string $message = 'Bike updated successfully',
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}
