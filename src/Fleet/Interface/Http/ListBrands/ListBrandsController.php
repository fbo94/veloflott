<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListBrands;

use Fleet\Application\ListBrands\ListBrandsHandler;
use Fleet\Application\ListBrands\ListBrandsQuery;
use Illuminate\Http\JsonResponse;

final class ListBrandsController
{
    public function __construct(
        private readonly ListBrandsHandler $handler,
    ) {}

    public function __invoke(): JsonResponse
    {
        $query = new ListBrandsQuery;

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
