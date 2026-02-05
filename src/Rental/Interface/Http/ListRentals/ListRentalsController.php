<?php

declare(strict_types=1);

namespace Rental\Interface\Http\ListRentals;

use Rental\Application\ListRentals\ListRentalsHandler;
use Rental\Application\ListRentals\ListRentalsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ListRentalsController
{
    public function __construct(
        private readonly ListRentalsHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['nullable', 'uuid', 'exists:customers,id'],
            'status' => ['nullable', 'string', 'in:pending,active,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = new ListRentalsQuery(
            customerId: $validated['customer_id'] ?? null,
            status: $validated['status'] ?? null,
            startDate: $validated['start_date'] ?? null,
            endDate: $validated['end_date'] ?? null,
            page: $validated['page'] ?? 1,
            perPage: $validated['per_page'] ?? 20,
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
