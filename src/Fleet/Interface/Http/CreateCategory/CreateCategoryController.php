<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateCategory;

use Fleet\Application\CreateCategory\CreateCategoryCommand;
use Fleet\Application\CreateCategory\CreateCategoryHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateCategoryController
{
    public function __construct(
        private readonly CreateCategoryHandler $handler,
    ) {
    }

    public function __invoke(CreateCategoryRequest $request): JsonResponse
    {
        $command = new CreateCategoryCommand(
            name: $request->input('name'),
            slug: $request->input('slug'),
            description: $request->input('description'),
            parentId: $request->input('parent_id'),
        );

        $categoryId = $this->handler->handle($command);

        return new JsonResponse(['id' => $categoryId], Response::HTTP_CREATED);
    }
}
