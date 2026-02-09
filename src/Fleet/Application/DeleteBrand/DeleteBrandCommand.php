<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteBrand;

final readonly class DeleteBrandCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
