<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetUtilizationKpi;

use Dashboard\Application\GetUtilizationKpi\GetUtilizationKpiHandler;
use Dashboard\Application\GetUtilizationKpi\GetUtilizationKpiQuery;
use Dashboard\Interface\Http\GetPerformanceIndicators\GetPerformanceIndicatorsRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetUtilizationKpiController
{
    public function __construct(
        private readonly GetUtilizationKpiHandler $handler,
    ) {}

    public function __invoke(GetPerformanceIndicatorsRequest $request): JsonResponse
    {
        $query = new GetUtilizationKpiQuery(
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
