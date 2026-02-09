<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\GetModelDetail;

use Fleet\Application\GetModelDetail\GetModelDetailHandler;
use Fleet\Application\GetModelDetail\GetModelDetailQuery;
use Fleet\Application\GetModelDetail\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

final readonly class GetModelDetailController
{
    public function __construct(
        private GetModelDetailHandler $handler,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        try {
            $query = new GetModelDetailQuery(modelId: $id);
            $response = $this->handler->handle($query);

            return response()->json($response->toArray());
        } catch (ModelNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
