<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeDetail;

use Fleet\Domain\BikeRepositoryInterface;

final class GetBikeDetailHandler
{
    public function __construct(
        private readonly BikeRepositoryInterface $bikes,
    ) {}

    public function handle(GetBikeDetailQuery $query): GetBikeDetailResponse
    {
        $bikeModel = $this->bikes->findByIdWithRelations($query->id);

        if ($bikeModel === null) {
            throw new BikeNotFoundException($query->id);
        }

        return GetBikeDetailResponse::fromEloquentModel($bikeModel);
    }
}
