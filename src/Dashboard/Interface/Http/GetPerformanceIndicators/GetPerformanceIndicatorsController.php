<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetPerformanceIndicators;

use Dashboard\Application\GetPerformanceIndicators\GetPerformanceIndicatorsHandler;
use Dashboard\Application\GetPerformanceIndicators\GetPerformanceIndicatorsQuery;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetPerformanceIndicatorsController
{
    public function __construct(
        private readonly GetPerformanceIndicatorsHandler $handler,
    ) {
    }

    public function __invoke(GetPerformanceIndicatorsRequest $request): JsonResponse
    {
        $query = new GetPerformanceIndicatorsQuery(
            dateFrom: $request->input('date_from') !== null
                ? new \DateTimeImmutable($request->input('date_from'))
                : null,
            dateTo: $request->input('date_to') !== null
                ? new \DateTimeImmutable($request->input('date_to'))
                : null,
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
