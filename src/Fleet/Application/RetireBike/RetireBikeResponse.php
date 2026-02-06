<?php

declare(strict_types=1);

namespace Fleet\Application\RetireBike;

final readonly class RetireBikeResponse
{
    public function __construct(
        public string $id,
        public string $message = 'Bike retired successfully',
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
        ];
    }
}
