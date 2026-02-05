<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdateBrand;

use Fleet\Application\UpdateBrand\BrandNotFoundException;
use Fleet\Application\UpdateBrand\UpdateBrandCommand;
use Fleet\Application\UpdateBrand\UpdateBrandHandler;
use Illuminate\Http\JsonResponse;

final readonly class UpdateBrandController
{
    public function __construct(
        private UpdateBrandHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateBrandRequest $request): JsonResponse
    {
        try {
            $command = new UpdateBrandCommand(
                id: $id,
                name: $request->validated('name'),
                logoUrl: $request->validated('logo_url'),
            );

            $response = $this->handler->handle($command);

            return response()->json($response->toArray());
        } catch (BrandNotFoundException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                404
            );
        }
    }
}
