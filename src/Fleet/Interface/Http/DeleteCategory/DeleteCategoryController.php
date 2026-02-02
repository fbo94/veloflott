<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\DeleteCategory;

use Fleet\Application\DeleteCategory\DeleteCategoryCommand;
use Fleet\Application\DeleteCategory\DeleteCategoryHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class DeleteCategoryController
{
    public function __construct(
        private readonly DeleteCategoryHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteCategoryCommand($id);

        $this->handler->handle($command);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
