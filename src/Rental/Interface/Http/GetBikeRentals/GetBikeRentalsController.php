<?php

declare(strict_types=1);

namespace Rental\Interface\Http\GetBikeRentals;

use Illuminate\Http\JsonResponse;
use Rental\Application\GetBikeRentals\GetBikeRentalsHandler;
use Rental\Application\GetBikeRentals\GetBikeRentalsQuery;

final readonly class GetBikeRentalsController
{
    public function __construct(
        private GetBikeRentalsHandler $handler,
    ) {}

    public function __invoke(GetBikeRentalsRequest $request, string $bikeId): JsonResponse
    {
        $query = new GetBikeRentalsQuery(
            bikeId: $bikeId,
            filter: $request->input('filter'),
        );

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
