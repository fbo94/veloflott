<?php

declare(strict_types=1);

namespace Customer\Interface\Http\ToggleRiskyFlag;

use Customer\Application\ToggleRiskyFlag\ToggleRiskyFlagCommand;
use Customer\Application\ToggleRiskyFlag\ToggleRiskyFlagHandler;
use Illuminate\Http\JsonResponse;

final readonly class ToggleRiskyFlagController
{
    public function __construct(
        private ToggleRiskyFlagHandler $handler,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $command = new ToggleRiskyFlagCommand(customerId: $id);
        $response = $this->handler->handle($command);

        return response()->json($response->toArray(), 200);
    }
}
