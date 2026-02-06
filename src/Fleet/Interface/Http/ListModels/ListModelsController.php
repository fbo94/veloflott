<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListModels;

use Fleet\Application\ListModels\ListModelsHandler;
use Fleet\Application\ListModels\ListModelsQuery;
use Illuminate\Http\JsonResponse;

final class ListModelsController
{
    public function __construct(
        private readonly ListModelsHandler $handler,
    ) {}

    public function __invoke(ListModelsRequest $request): JsonResponse
    {
        $query = new ListModelsQuery(
            brandId: $request->input('brand_id'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
