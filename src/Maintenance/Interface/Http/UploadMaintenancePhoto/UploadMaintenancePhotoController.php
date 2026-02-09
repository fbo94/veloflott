<?php

declare(strict_types=1);

namespace Maintenance\Interface\Http\UploadMaintenancePhoto;

use Illuminate\Http\JsonResponse;
use Maintenance\Application\UploadMaintenancePhoto\MaintenanceNotFoundException;
use Maintenance\Application\UploadMaintenancePhoto\UploadMaintenancePhotoCommand;
use Maintenance\Application\UploadMaintenancePhoto\UploadMaintenancePhotoHandler;

final readonly class UploadMaintenancePhotoController
{
    public function __construct(
        private UploadMaintenancePhotoHandler $handler,
    ) {
    }

    public function __invoke(string $id, UploadMaintenancePhotoRequest $request): JsonResponse
    {
        try {
            $command = new UploadMaintenancePhotoCommand(
                maintenanceId: $id,
                photo: $request->file('photo'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 201);
        } catch (MaintenanceNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
