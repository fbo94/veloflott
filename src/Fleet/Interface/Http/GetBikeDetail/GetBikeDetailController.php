<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\GetBikeDetail;

use Fleet\Application\GetBikeDetail\GetBikeDetailHandler;
use Fleet\Application\GetBikeDetail\GetBikeDetailQuery;
use Illuminate\Http\JsonResponse;

final class GetBikeDetailController
{
    public function __construct(
        private readonly GetBikeDetailHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $query = new GetBikeDetailQuery($id);

        $response = $this->handler->handle($query);

        return new JsonResponse($response->toArray());
    }
}
