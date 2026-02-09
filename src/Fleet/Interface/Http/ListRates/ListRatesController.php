<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListRates;

use Fleet\Application\ListRates\ListRatesHandler;
use Fleet\Application\ListRates\ListRatesQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListRatesController
{
    public function __construct(
        private readonly ListRatesHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListRatesQuery(
            categoryId: $request->query('category_id'),
            bikeId: $request->query('bike_id'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
