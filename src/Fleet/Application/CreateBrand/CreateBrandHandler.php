<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBrand;

use Fleet\Domain\Brand;
use Fleet\Domain\BrandRepositoryInterface;
use Illuminate\Support\Str;

final readonly class CreateBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    public function handle(CreateBrandCommand $command): CreateBrandResponse
    {
        $brand = new Brand(
            id: Str::uuid()->toString(),
            name: $command->name,
            logoUrl: $command->logoUrl,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );

        $this->brandRepository->save($brand);

        return new CreateBrandResponse(
            id: $brand->id(),
            name: $brand->name(),
            logoUrl: $brand->logoUrl(),
        );
    }
}
