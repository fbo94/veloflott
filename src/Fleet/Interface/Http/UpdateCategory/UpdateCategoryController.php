<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateCategory;

use Fleet\Application\UpdateCategory\UpdateCategoryCommand;
use Fleet\Application\UpdateCategory\UpdateCategoryHandler;
use Illuminate\Http\JsonResponse;

final class UpdateCategoryController
{
    public function __construct(
        private readonly UpdateCategoryHandler $handler,
    ) {}

    public function __invoke(string $id, UpdateCategoryRequest $request): JsonResponse
    {
        $command = new UpdateCategoryCommand(
            id: $id,
            name: $request->input('name'),
            slug: $request->input('slug'),
            description: $request->input('description'),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray());
    }
}
