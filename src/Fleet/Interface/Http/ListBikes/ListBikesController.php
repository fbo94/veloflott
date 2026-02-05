<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListBikes;

use Fleet\Application\ListBikes\ListBikesHandler;
use Fleet\Application\ListBikes\ListBikesQuery;
use Illuminate\Http\JsonResponse;

final class ListBikesController
{
    public function __construct(
        private readonly ListBikesHandler $handler,
    ) {
    }

    public function __invoke(ListBikesRequest $request): JsonResponse
    {
        $query = new ListBikesQuery(
            statuses: $request->input('statuses'),
            categoryIds: $request->input('category_ids'),
            frameSizes: $request->input('frame_sizes'),
            includeRetired: $request->boolean('include_retired', false),
            search: $request->input('search'),
            sortBy: $request->input('sort_by', 'internal_number'),
            sortDirection: $request->input('sort_direction', 'asc'),
            page: $request->integer('page', 1),
            perPage: $request->integer('per_page', 50),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
