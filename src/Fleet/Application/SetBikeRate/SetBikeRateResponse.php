<?php

declare(strict_types=1);

namespace Fleet\Application\SetBikeRate;

final readonly class SetBikeRateResponse
{
    public function __construct(
        public string $rateId,
        public string $bikeId,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->rateId,
            'bike_id' => $this->bikeId,
            'message' => $this->message,
        ];
    }
}
