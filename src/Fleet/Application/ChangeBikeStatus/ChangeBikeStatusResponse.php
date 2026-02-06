<?php

declare(strict_types=1);

namespace Fleet\Application\ChangeBikeStatus;

final readonly class ChangeBikeStatusResponse
{
    public function __construct(
        public string $id,
        public string $message = 'Bike status changed successfully',
    ) {}

    /**
     * @return array{id: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}
