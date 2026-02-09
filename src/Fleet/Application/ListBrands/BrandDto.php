<?php

declare(strict_types=1);

namespace Fleet\Application\ListBrands;

use Fleet\Domain\Brand;

final readonly class BrandDto
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $logoUrl,
    ) {
    }

    public static function fromBrand(Brand $brand): self
    {
        return new self(
            id: $brand->id(),
            name: $brand->name(),
            logoUrl: $brand->logoUrl(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logoUrl,
        ];
    }
}
