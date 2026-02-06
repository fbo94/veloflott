<?php

declare(strict_types=1);

namespace Rental\Interface\Http\ListActiveRentals;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Rental\Application\ListActiveRentals\ListActiveRentalsHandler;
use Rental\Application\ListActiveRentals\ListActiveRentalsQuery;

final class ListActiveRentalsController
{
    public function __construct(
        private readonly ListActiveRentalsHandler $handler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $query = new ListActiveRentalsQuery(
            customerId: $request->query('customer_id'),
            bikeId: $request->query('bike_id'),
            onlyLate: $request->boolean('only_late', false),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
