<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBrand;

final readonly class UpdateBrandCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $logoUrl = null,
    ) {
    }
}
