<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateModel;

use Fleet\Application\CreateModel\CreateModelCommand;
use Fleet\Application\CreateModel\CreateModelHandler;
use Illuminate\Http\JsonResponse;

final readonly class CreateModelController
{
    public function __construct(
        private CreateModelHandler $handler,
    ) {
    }

    public function __invoke(CreateModelRequest $request): JsonResponse
    {
        $command = new CreateModelCommand(
            name: $request->validated('name'),
            brandId: $request->validated('brand_id'),
        );

        $response = $this->handler->handle($command);

        return response()->json($response->toArray(), 201);
    }
}
