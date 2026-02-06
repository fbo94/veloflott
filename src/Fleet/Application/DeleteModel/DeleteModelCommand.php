<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteModel;

final readonly class DeleteModelCommand
{
    public function __construct(
        public string $id,
    ) {}
}
