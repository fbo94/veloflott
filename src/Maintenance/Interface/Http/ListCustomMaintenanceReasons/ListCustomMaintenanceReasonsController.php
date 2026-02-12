<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\ListCustomMaintenanceReasons;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maintenance\Application\ListCustomMaintenanceReasons\ListCustomMaintenanceReasonsHandler;
use Maintenance\Application\ListCustomMaintenanceReasons\ListCustomMaintenanceReasonsQuery;
use Symfony\Component\HttpFoundation\Response;

final class ListCustomMaintenanceReasonsController
{
    public function __construct(
        private readonly ListCustomMaintenanceReasonsHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListCustomMaintenanceReasonsQuery(
            category: $request->query('category'),
            isActive: $request->query('is_active') !== null
                ? filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN)
                : null,
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
