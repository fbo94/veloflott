<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\DeleteCustomMaintenanceReason;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\CreateCustomMaintenanceReason\CustomMaintenanceReasonNotFoundException;
use Maintenance\Application\DeleteCustomMaintenanceReason\DeleteCustomMaintenanceReasonCommand;
use Maintenance\Application\DeleteCustomMaintenanceReason\DeleteCustomMaintenanceReasonHandler;
use Symfony\Component\HttpFoundation\Response;

final class DeleteCustomMaintenanceReasonController
{
    public function __construct(
        private readonly DeleteCustomMaintenanceReasonHandler $handler,
    ) {
    }

    /**
     * @throws CustomMaintenanceReasonNotFoundException
     */
    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteCustomMaintenanceReasonCommand($id);

        $this->handler->handle($command);

        return new JsonResponse([
            'message' => 'Custom maintenance reason deleted successfully',
        ], Response::HTTP_OK);
    }
}
