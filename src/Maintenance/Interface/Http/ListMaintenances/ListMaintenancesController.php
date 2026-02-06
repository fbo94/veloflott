<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\ListMaintenances;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\ListMaintenances\ListMaintenancesHandler;
use Maintenance\Application\ListMaintenances\ListMaintenancesQuery;
use Symfony\Component\HttpFoundation\Response;

final class ListMaintenancesController
{
    public function __construct(
        private readonly ListMaintenancesHandler $handler,
    ) {}

    public function __invoke(ListMaintenancesRequest $request): JsonResponse
    {
        $query = new ListMaintenancesQuery(
            bikeId: $request->input('bike_id'),
            status: $request->input('status'),
            priority: $request->input('priority'),
            dateFrom: $request->input('date_from'),
            dateTo: $request->input('date_to'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
