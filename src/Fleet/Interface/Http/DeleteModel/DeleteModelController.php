<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\DeleteModel;

use Fleet\Application\DeleteModel\DeleteModelCommand;
use Fleet\Application\DeleteModel\DeleteModelHandler;
use Illuminate\Http\JsonResponse;

final readonly class DeleteModelController
{
    public function __construct(
        private DeleteModelHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteModelCommand(id: $id);
        $this->handler->handle($command);

        return response()->json(null, 204);
    }
}
