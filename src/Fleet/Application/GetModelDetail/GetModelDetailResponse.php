<?php

declare(strict_types=1);

namespace Fleet\Application\GetModelDetail;

final readonly class GetModelDetailResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brandId,
        public string $brandName,
        public ?string $brandLogoUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand_id' => $this->brandId,
            'brand_name' => $this->brandName,
            'brand_logo_url' => $this->brandLogoUrl,
        ];
    }
}
