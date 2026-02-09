<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\UpdateDuration;

use Illuminate\Http\JsonResponse;
use Pricing\Application\UpdateDuration\UpdateDurationCommand;
use Pricing\Application\UpdateDuration\UpdateDurationHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class UpdateDurationController
{
    public function __construct(
        private UpdateDurationHandler $handler,
    ) {
    }

    public function __invoke(string $id, UpdateDurationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new UpdateDurationCommand(
            id: $id,
            code: $validated['code'],
            label: $validated['label'],
            durationHours: $validated['duration_hours'] ?? null,
            durationDays: $validated['duration_days'] ?? null,
            isCustom: $validated['is_custom'] ?? false,
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
