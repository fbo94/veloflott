<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateRate;

use Fleet\Application\UpdateRate\UpdateRateCommand;
use Fleet\Application\UpdateRate\UpdateRateHandler;
use Fleet\Domain\RateDuration;
use Illuminate\Http\JsonResponse;

final class UpdateRateController
{
    public function __construct(
        private readonly UpdateRateHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateRateRequest $request): JsonResponse
    {
        $command = new UpdateRateCommand(
            id: $id,
            duration: RateDuration::from($request->input('duration')),
            price: (float) $request->input('price'),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray());
    }
}
