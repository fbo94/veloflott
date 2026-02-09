<?php

declare(strict_types=1);

namespace Fleet\Application\ListBrands;

final readonly class ListBrandsResponse
{
    /**
     * @param  array<BrandDto>  $brands
     */
    public function __construct(
        public array $brands,
    ) {
    }

    public function toArray(): array
    {
        return [
            'brands' => array_map(
                fn (BrandDto $brand) => $brand->toArray(),
                $this->brands
            ),
        ];
    }
}
