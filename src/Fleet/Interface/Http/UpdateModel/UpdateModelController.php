<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateModel;

use Fleet\Application\UpdateModel\ModelNotFoundException;
use Fleet\Application\UpdateModel\UpdateModelCommand;
use Fleet\Application\UpdateModel\UpdateModelHandler;
use Illuminate\Http\JsonResponse;

final readonly class UpdateModelController
{
    public function __construct(
        private UpdateModelHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateModelRequest $request): JsonResponse
    {
        try {
            $command = new UpdateModelCommand(
                id: $id,
                name: $request->validated('name'),
                brandId: $request->validated('brand_id'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray());
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
