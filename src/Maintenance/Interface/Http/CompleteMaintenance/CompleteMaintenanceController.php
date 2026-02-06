<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\CompleteMaintenance;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceCommand;
use Maintenance\Application\CompleteMaintenance\CompleteMaintenanceHandler;
use Maintenance\Domain\MaintenanceException;
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
            photos: $request->input('photos', []),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
