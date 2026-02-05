<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\CompleteMaintenance;

use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceCommand;
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceHandler;
use Maintenance\Domain\MaintenanceException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CompleteMaintenanceController
{
    public function __construct(
        private readonly CompleteMaintenanceHandler $handler,
    ) {}

    /**
     * @throws MaintenanceException
     */
    public function __invoke(string $id, CompleteMaintenanceRequest $request): JsonResponse
    {
        $command = new CompleteMaintenanceCommand(
            maintenanceId: $id,
            workDescription: $request->input('work_description'),
            partsReplaced: $request->input('parts_replaced'),
            cost: $request->input('cost'),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse([
            'maintenance_id' => $response->maintenanceId,
            'bike_id' => $response->bikeId,
            'message' => $response->message,
        ], Response::HTTP_OK);
    }
}
