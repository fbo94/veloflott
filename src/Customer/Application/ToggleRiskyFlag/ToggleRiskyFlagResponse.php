<?php

declare(strict_types=1);

namespace Customer\Application\ToggleRiskyFlag;

final readonly class ToggleRiskyFlagResponse
{
    public function __construct(
        public string $customerId,
        public bool $isRisky,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'is_risky' => $this->isRisky,
            'message' => $this->message,
        ];
    }
}
