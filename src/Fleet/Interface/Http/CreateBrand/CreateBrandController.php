<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateBrand;

use Fleet\Application\CreateBrand\CreateBrandCommand;
use Fleet\Application\CreateBrand\CreateBrandHandler;
use Illuminate\Http\JsonResponse;

final readonly class CreateBrandController
{
    public function __construct(
        private CreateBrandHandler $handler,
    ) {
    }

    public function __invoke(CreateBrandRequest $request): JsonResponse
    {
        $command = new CreateBrandCommand(
            name: $request->validated('name'),
            logoUrl: $request->validated('logo_url'),
        );

        $response = $this->handler->handle($command);

        return response()->json($response->toArray(), 201);
    }
}
