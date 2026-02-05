<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListCategories;

use Fleet\Application\ListCategories\ListCategoriesHandler;
use Fleet\Application\ListCategories\ListCategoriesQuery;
use Illuminate\Http\JsonResponse;

final class ListCategoriesController
{
    public function __construct(
        private readonly ListCategoriesHandler $handler,
    ) {}

    public function __invoke(): JsonResponse
    {
        $query = new ListCategoriesQuery();

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
