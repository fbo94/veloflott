<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\GetBikeMaintenanceHistory;

use Fleet\Domain\BikeException;
use Illuminate\Http\JsonResponse;
use Maintenance\Application\GetBikeMaintenanceHistory\GetBikeMaintenanceHistoryHandler;
use Maintenance\Application\GetBikeMaintenanceHistory\GetBikeMaintenanceHistoryQuery;
use Symfony\Component\HttpFoundation\Response;

final class GetBikeMaintenanceHistoryController
{
    public function __construct(
        private readonly GetBikeMaintenanceHistoryHandler $handler,
    ) {
    }

    /**
     * @throws BikeException
     */
    public function __invoke(string $bikeId): JsonResponse
    {
        $query = new GetBikeMaintenanceHistoryQuery(bikeId: $bikeId);

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
