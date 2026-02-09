<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\DeleteBikePhoto;

use Fleet\Application\DeleteBikePhoto\BikeNotFoundException;
use Fleet\Application\DeleteBikePhoto\DeleteBikePhotoCommand;
use Fleet\Application\DeleteBikePhoto\DeleteBikePhotoHandler;
use Illuminate\Http\JsonResponse;

final readonly class DeleteBikePhotoController
{
    public function __construct(
        private DeleteBikePhotoHandler $handler,
    ) {
    }

    public function __invoke(string $id, DeleteBikePhotoRequest $request): JsonResponse
    {
        try {
            $command = new DeleteBikePhotoCommand(
                bikeId: $id,
                photoUrl: $request->validated('photo_url'),
            );

            $this->handler->handle($command);

            return response()->json(null, 204);
        } catch (BikeNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
