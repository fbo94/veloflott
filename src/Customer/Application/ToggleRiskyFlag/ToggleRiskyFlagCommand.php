<?php

declare(strict_types=1);

namespace Customer\Application\ToggleRiskyFlag;

final readonly class ToggleRiskyFlagCommand
{
    public function __construct(
        public string $customerId,
    ) {}
}
