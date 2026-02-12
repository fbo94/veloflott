<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\GetCustomMaintenanceReasonDetail;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Application\GetCustomMaintenanceReasonDetail\GetCustomMaintenanceReasonDetailHandler;
use Maintenance\Application\GetCustomMaintenanceReasonDetail\GetCustomMaintenanceReasonDetailQuery;
use Symfony\Component\HttpFoundation\Response;

final class GetCustomMaintenanceReasonDetailController
{
    public function __construct(
        private readonly GetCustomMaintenanceReasonDetailHandler $handler,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function __invoke(string $id): JsonResponse
    {
        $query = new GetCustomMaintenanceReasonDetailQuery($id);

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
