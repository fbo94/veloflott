<?php

declare(strict_types=1);

namespace Dashboard\Interface\Http\GetMaintenanceKpi;

use Dashboard\Application\GetMaintenanceKpi\GetMaintenanceKpiHandler;
use Dashboard\Application\GetMaintenanceKpi\GetMaintenanceKpiQuery;
use Dashboard\Interface\Http\GetPerformanceIndicators\GetPerformanceIndicatorsRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetMaintenanceKpiController
{
    public function __construct(
        private readonly GetMaintenanceKpiHandler $handler,
    ) {}

    public function __invoke(GetPerformanceIndicatorsRequest $request): JsonResponse
    {
        $query = new GetMaintenanceKpiQuery(
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
