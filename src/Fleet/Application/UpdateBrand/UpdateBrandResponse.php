<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBrand;

final readonly class UpdateBrandResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $logoUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logoUrl,
        ];
    }
}
