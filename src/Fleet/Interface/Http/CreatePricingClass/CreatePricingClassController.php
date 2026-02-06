<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreatePricingClass;

use Fleet\Application\CreatePricingClass\CreatePricingClassCommand;
use Fleet\Application\CreatePricingClass\CreatePricingClassHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreatePricingClassController
{
    public function __construct(
        private readonly CreatePricingClassHandler $handler,
    ) {}

    public function __invoke(CreatePricingClassRequest $request): JsonResponse
    {
        $command = new CreatePricingClassCommand(
            code: $request->input('code'),
            label: $request->input('label'),
            description: $request->input('description'),
            color: $request->input('color'),
            sortOrder: $request->input('sort_order', 0),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
