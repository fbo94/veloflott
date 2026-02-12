<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\CreateCustomMaintenanceReason;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\CreateCustomMaintenanceReason\CreateCustomMaintenanceReasonCommand;
use Maintenance\Application\CreateCustomMaintenanceReason\CreateCustomMaintenanceReasonHandler;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Symfony\Component\HttpFoundation\Response;

final class CreateCustomMaintenanceReasonController
{
    public function __construct(
        private readonly CreateCustomMaintenanceReasonHandler $handler,
    ) {
    }

    /**
     * @throws MaintenanceException
     */
    public function __invoke(CreateCustomMaintenanceReasonRequest $request): JsonResponse
    {
        $command = new CreateCustomMaintenanceReasonCommand(
            code: $request->input('code'),
            label: $request->input('label'),
            description: $request->input('description'),
            category: $request->input('category'),
            isActive: (bool) $request->input('is_active', true),
            sortOrder: (int) $request->input('sort_order', 0),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
