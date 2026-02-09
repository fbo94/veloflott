<?php

declare(strict_types=1);

namespace Pricing\Interface\Http\CreateDuration;

use Illuminate\Http\JsonResponse;
use Pricing\Application\CreateDuration\CreateDurationCommand;
use Pricing\Application\CreateDuration\CreateDurationHandler;
use Symfony\Component\HttpFoundation\Response;

final readonly class CreateDurationController
{
    public function __construct(
        private CreateDurationHandler $handler,
    ) {
    }

    public function __invoke(CreateDurationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $command = new CreateDurationCommand(
            code: $validated['code'],
            label: $validated['label'],
            durationHours: $validated['duration_hours'] ?? null,
            durationDays: $validated['duration_days'] ?? null,
            isCustom: $validated['is_custom'] ?? false,
            sortOrder: $validated['sort_order'] ?? 0,
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
