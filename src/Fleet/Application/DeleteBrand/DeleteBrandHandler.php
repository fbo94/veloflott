<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteBrand;

use Fleet\Domain\BrandRepositoryInterface;

final readonly class DeleteBrandHandler
{
    public function __construct(
        private BrandRepositoryInterface $brandRepository,
    ) {
    }

    public function handle(DeleteBrandCommand $command): void
    {
        $this->brandRepository->delete($command->id);
    }
}
