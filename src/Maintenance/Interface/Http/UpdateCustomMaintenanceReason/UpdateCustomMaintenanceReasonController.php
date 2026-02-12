<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\UpdateCustomMaintenanceReason;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Application\UpdateCustomMaintenanceReason\UpdateCustomMaintenanceReasonCommand;
use Maintenance\Application\UpdateCustomMaintenanceReason\UpdateCustomMaintenanceReasonHandler;
use Symfony\Component\HttpFoundation\Response;

final class UpdateCustomMaintenanceReasonController
{
    public function __construct(
        private readonly UpdateCustomMaintenanceReasonHandler $handler,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function __invoke(string $id, UpdateCustomMaintenanceReasonRequest $request): JsonResponse
    {
        $command = new UpdateCustomMaintenanceReasonCommand(
            id: $id,
            label: $request->input('label'),
            description: $request->input('description'),
            category: $request->input('category'),
            isActive: (bool) $request->input('is_active'),
            sortOrder: (int) $request->input('sort_order'),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
