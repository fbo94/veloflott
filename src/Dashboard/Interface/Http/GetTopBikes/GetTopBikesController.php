<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetTopBikes;

use Dashboard\Application\GetTopBikes\GetTopBikesHandler;
use Dashboard\Application\GetTopBikes\GetTopBikesQuery;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetTopBikesController
{
    public function __construct(
        private readonly GetTopBikesHandler $handler,
    ) {}

    public function __invoke(GetTopBikesRequest $request): JsonResponse
    {
        $query = new GetTopBikesQuery(
            limit: $request->input('limit', 10),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
