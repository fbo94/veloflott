<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\UpdatePricingClass;

use Fleet\Application\UpdatePricingClass\UpdatePricingClassCommand;
use Fleet\Application\UpdatePricingClass\UpdatePricingClassHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class UpdatePricingClassController
{
    public function __construct(
        private readonly UpdatePricingClassHandler $handler,
    ) {
    }

    public function __invoke(string $pricingClassId, UpdatePricingClassRequest $request): JsonResponse
    {
        $command = new UpdatePricingClassCommand(
            id: $pricingClassId,
            code: $request->input('code'),
            label: $request->input('label'),
            description: $request->input('description'),
            color: $request->input('color'),
            sortOrder: $request->input('sort_order', 0),
            isActive: $request->input('is_active', true),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_OK);
    }
}
