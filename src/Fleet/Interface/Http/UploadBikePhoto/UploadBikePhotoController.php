<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UploadBikePhoto;

use Fleet\Application\UploadBikePhoto\BikeNotFoundException;
use Fleet\Application\UploadBikePhoto\UploadBikePhotoCommand;
use Fleet\Application\UploadBikePhoto\UploadBikePhotoHandler;
use Illuminate\Http\JsonResponse;

final readonly class UploadBikePhotoController
{
    public function __construct(
        private UploadBikePhotoHandler $handler,
    ) {}

    public function __invoke(string $id, UploadBikePhotoRequest $request): JsonResponse
    {
        try {
            $command = new UploadBikePhotoCommand(
                bikeId: $id,
                photo: $request->file('photo'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 201);
        } catch (BikeNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
