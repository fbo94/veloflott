<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\DeleteBrand;

use Fleet\Application\DeleteBrand\DeleteBrandCommand;
use Fleet\Application\DeleteBrand\DeleteBrandHandler;
use Illuminate\Http\JsonResponse;

final readonly class DeleteBrandController
{
    public function __construct(
        private DeleteBrandHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteBrandCommand(id: $id);
        $this->handler->handle($command);

        return response()->json(null, 204);
    }
}
