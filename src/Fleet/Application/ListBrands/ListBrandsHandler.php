<?php

declare(strict_types=1);

namespace Fleet\Application\ListBrands;

use Fleet\Domain\BrandRepositoryInterface;

final class ListBrandsHandler
{
    public function __construct(
        private readonly BrandRepositoryInterface $brands,
    ) {}

    public function handle(ListBrandsQuery $query): ListBrandsResponse
    {
        $brands = $this->brands->findAll();

        $brandDtos = array_map(
            fn ($brand) => BrandDto::fromBrand($brand),
            $brands
        );

        return new ListBrandsResponse($brandDtos);
    }
}
