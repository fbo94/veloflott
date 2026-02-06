<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\GetBikeRate;

use Fleet\Application\GetBikeRate\GetBikeRateHandler;
use Fleet\Application\GetBikeRate\GetBikeRateQuery;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetBikeRateController
{
    public function __construct(
        private readonly GetBikeRateHandler $handler,
    ) {}

    public function __invoke(string $bikeId): JsonResponse
    {
        $query = new GetBikeRateQuery(bikeId: $bikeId);

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
