<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteRate;

final readonly class DeleteRateCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
