<?php

declare(strict_types=1);

namespace Fleet\Application\GetModelDetail;

use Fleet\Domain\BrandRepositoryInterface;
use Fleet\Domain\ModelRepositoryInterface;

final readonly class GetModelDetailHandler
{
    public function __construct(
        private ModelRepositoryInterface $modelRepository,
        private BrandRepositoryInterface $brandRepository,
    ) {}

    public function handle(GetModelDetailQuery $query): GetModelDetailResponse
    {
        $model = $this->modelRepository->findById($query->modelId);

        if ($model === null) {
            throw new ModelNotFoundException($query->modelId);
        }

        $brand = $this->brandRepository->findById($model->brandId());

        return new GetModelDetailResponse(
            id: $model->id(),
            name: $model->name(),
            brandId: $model->brandId(),
            brandName: $brand?->name() ?? '',
            brandLogoUrl: $brand?->logoUrl(),
        );
    }
}
