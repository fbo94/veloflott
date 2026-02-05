<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetRevenueKpi;

use Dashboard\Application\GetRevenueKpi\GetRevenueKpiHandler;
use Dashboard\Application\GetRevenueKpi\GetRevenueKpiQuery;
use Dashboard\Interface\Http\GetPerformanceIndicators\GetPerformanceIndicatorsRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetRevenueKpiController
{
    public function __construct(
        private readonly GetRevenueKpiHandler $handler,
    ) {}

    public function __invoke(GetPerformanceIndicatorsRequest $request): JsonResponse
    {
        $query = new GetRevenueKpiQuery(
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
