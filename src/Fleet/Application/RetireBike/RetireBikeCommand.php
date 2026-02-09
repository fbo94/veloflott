<?php

declare(strict_types=1);

namespace Fleet\Application\RetireBike;

final readonly class RetireBikeCommand
{
    public function __construct(
        public string $bikeId,
        public string $reason,
        public ?string $comment = null,
    ) {
    }
}
