<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\ListMaintenanceReasons;

use Maintenance\Application\ListMaintenanceReasons\ListMaintenanceReasonsHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ListMaintenanceReasonsController
{
    public function __construct(
        private readonly ListMaintenanceReasonsHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $response = $this->handler->handle();

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
