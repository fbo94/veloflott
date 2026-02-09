<?php

declare(strict_types=1);

namespace Pricing\Application\DeleteDuration;

final readonly class DeleteDurationCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
