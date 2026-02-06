<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\DeclareMaintenance;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\DeclareMaintenance\DeclareMaintenanceCommand;
use Maintenance\Application\DeclareMaintenance\DeclareMaintenanceHandler;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Symfony\Component\HttpFoundation\Response;

final class DeclareMaintenanceController
{
    public function __construct(
        private readonly DeclareMaintenanceHandler $handler,
    ) {
    }

    /**
     * @throws MaintenanceException
     */
    public function __invoke(DeclareMaintenanceRequest $request): JsonResponse
    {
        $command = new DeclareMaintenanceCommand(
            bikeId: $request->input('bike_id'),
            type: $request->input('type'),
            reason: $request->input('reason'),
            priority: $request->input('priority'),
            description: $request->input('description'),
            scheduledAt: $request->input('scheduled_at') !== null
                ? new \DateTimeImmutable($request->input('scheduled_at'))
                : null,
            photos: $request->input('photos', []) ?? [],
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
