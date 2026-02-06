<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\GetActiveSizeMappingConfiguration;

use Fleet\Application\GetActiveSizeMappingConfiguration\GetActiveSizeMappingConfigurationHandler;
use Fleet\Application\GetActiveSizeMappingConfiguration\GetActiveSizeMappingConfigurationQuery;
use Illuminate\Http\JsonResponse;

final readonly class GetActiveSizeMappingConfigurationController
{
    public function __construct(
        private GetActiveSizeMappingConfigurationHandler $handler,
    ) {}

    public function __invoke(): JsonResponse
    {
        try {
            $query = new GetActiveSizeMappingConfigurationQuery;
            $response = $this->handler->handle($query);

            return response()->json($response->toArray(), 200);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
