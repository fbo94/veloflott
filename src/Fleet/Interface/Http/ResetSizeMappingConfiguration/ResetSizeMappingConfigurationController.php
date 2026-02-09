<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ResetSizeMappingConfiguration;

use Fleet\Application\ResetSizeMappingConfiguration\ResetSizeMappingConfigurationCommand;
use Fleet\Application\ResetSizeMappingConfiguration\ResetSizeMappingConfigurationHandler;
use Illuminate\Http\JsonResponse;

final readonly class ResetSizeMappingConfigurationController
{
    public function __construct(
        private ResetSizeMappingConfigurationHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        try {
            $command = new ResetSizeMappingConfigurationCommand();
            $response = $this->handler->handle($command);

            return response()->json($response->toArray(), 200);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
