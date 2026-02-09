<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBrand;

use Fleet\Domain\BrandRepositoryInterface;

final readonly class UpdateBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    /**
     * @throws BrandNotFoundException
     */
    public function handle(UpdateBrandCommand $command): UpdateBrandResponse
    {
        $brand = $this->brandRepository->findById($command->id);

        if ($brand === null) {
            throw new BrandNotFoundException($command->id);
        }

        $brand->rename($command->name);
        $brand->updateLogo($command->logoUrl);

        $this->brandRepository->save($brand);

        return new UpdateBrandResponse(
            id: $brand->id(),
            name: $brand->name(),
            logoUrl: $brand->logoUrl(),
        );
    }
}
