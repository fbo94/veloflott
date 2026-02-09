<?php

declare(strict_types=1);

namespace Fleet\Application\ListBikes;

use Fleet\Domain\BikeRepositoryInterface;

final class ListBikesHandler
{
    public function __construct(
        private readonly BikeRepositoryInterface $bikes,
    ) {
    }

    public function handle(ListBikesQuery $query): ListBikesResponse
    {
        $result = $this->bikes->findFiltered(
            statuses: $query->statuses,
            categoryIds: $query->categoryIds,
            frameSizes: $query->frameSizes,
            includeRetired: $query->includeRetired,
            search: $query->search,
            sortBy: $query->sortBy,
            sortDirection: $query->sortDirection,
            page: $query->page,
            perPage: $query->perPage,
        );

        $bikeDtos = array_map(
            fn ($bikeModel) => BikeDto::fromEloquentModel($bikeModel),
            $result['bikes']
        );

        return new ListBikesResponse(
            bikes: $bikeDtos,
            total: $result['total'],
            page: $query->page,
            perPage: $query->perPage,
        );
    }
}
