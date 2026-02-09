<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\UpdatePricingClass;

use Illuminate\Http\JsonResponse;
use Pricing\Application\UpdatePricingClass\UpdatePricingClassCommand;
use Pricing\Application\UpdatePricingClass\UpdatePricingClassHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class UpdatePricingClassController
{
    public function __construct(
        private UpdatePricingClassHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdatePricingClassRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new UpdatePricingClassCommand(
            id: $id,
            code: $validated['code'],
            label: $validated['label'],
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            sortOrder: $validated['sort_order'] ?? 0,
            isActive: $validated['is_active'] ?? true,
        );

        try {
            $response = $this->handler->handle($command);

            return new JsonResponse($response->toArray(), Response::HTTP_OK);
        } catch (\DomainException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
