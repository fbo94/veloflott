<?php

declare(strict_types=1);

namespace Customer\Interface\Http\SearchCustomers;

use Customer\Application\SearchCustomers\SearchCustomersHandler;
use Customer\Application\SearchCustomers\SearchCustomersQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchCustomersController
{
    public function __construct(
        private readonly SearchCustomersHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = new SearchCustomersQuery(
            search: $request->query('search'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
