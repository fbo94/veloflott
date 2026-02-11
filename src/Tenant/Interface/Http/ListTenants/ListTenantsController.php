<?php

declare(strict_types=1);

namespace Tenant\Interface\Http\ListTenants;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tenant\Application\ListTenants\ListTenantsHandler;
use Tenant\Application\ListTenants\ListTenantsQuery;

final readonly class ListTenantsController
{
    public function __construct(
        private ListTenantsHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListTenantsQuery(
            status: $request->query('status'),
            search: $request->query('search'),
        );

        $response = $this->handler->handle($query);

        return response()->json($response->toArray());
    }
}
