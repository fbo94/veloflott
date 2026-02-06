<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\StartMaintenance;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\StartMaintenance\StartMaintenanceCommand;
use Maintenance\Application\StartMaintenance\StartMaintenanceHandler;
use Maintenance\Domain\MaintenanceException;
use Symfony\Component\HttpFoundation\Response;

final class StartMaintenanceController
{
    public function __construct(
        private readonly StartMaintenanceHandler $handler,
    ) {}

    /**
     * @throws MaintenanceException
     */
    public function __invoke(string $id): JsonResponse
    {
        $command = new StartMaintenanceCommand(maintenanceId: $id);

        $response = $this->handler->handle($command);

        return new JsonResponse([
            'maintenance_id' => $response->maintenanceId,
            'message' => $response->message,
        ], Response::HTTP_OK);
    }
}
