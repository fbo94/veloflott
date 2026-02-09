<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\GetBikeStatusHistory;

use Fleet\Application\GetBikeStatusHistory\GetBikeStatusHistoryHandler;
use Fleet\Application\GetBikeStatusHistory\GetBikeStatusHistoryQuery;
use Fleet\Application\UpdateBike\BikeNotFoundException;
use Illuminate\Http\JsonResponse;

final readonly class GetBikeStatusHistoryController
{
    public function __construct(
        private GetBikeStatusHistoryHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $query = new GetBikeStatusHistoryQuery(bikeId: $id);
            $response = $this->handler->handle($query);

            return response()->json($response->toArray());
        } catch (BikeNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
