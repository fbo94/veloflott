<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBrand;

final readonly class CreateBrandCommand
{
    public function __construct(
        public string $name,
        public ?string $logoUrl = null,
    ) {
    }
}
