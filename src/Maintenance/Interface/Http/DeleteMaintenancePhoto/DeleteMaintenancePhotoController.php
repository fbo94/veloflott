<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\DeleteMaintenancePhoto;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\DeleteMaintenancePhoto\DeleteMaintenancePhotoCommand;
use Maintenance\Application\DeleteMaintenancePhoto\DeleteMaintenancePhotoHandler;
use Maintenance\Application\DeleteMaintenancePhoto\MaintenanceNotFoundException;

final readonly class DeleteMaintenancePhotoController
{
    public function __construct(
        private DeleteMaintenancePhotoHandler $handler,
    ) {
    }

    public function __invoke(string $id, DeleteMaintenancePhotoRequest $request): JsonResponse
    {
        try {
            $command = new DeleteMaintenancePhotoCommand(
                maintenanceId: $id,
                photoUrl: $request->validated('photo_url'),
            );

            $this->handler->handle($command);

            return response()->json(null, 204);
        } catch (MaintenanceNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
