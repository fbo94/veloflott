<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\CreateDuration;

use Fleet\Application\CreateDuration\CreateDurationCommand;
use Fleet\Application\CreateDuration\CreateDurationHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CreateDurationController
{
    public function __construct(
        private readonly CreateDurationHandler $handler,
    ) {
    }

    public function __invoke(CreateDurationRequest $request): JsonResponse
    {
        $command = new CreateDurationCommand(
            code: $request->input('code'),
            label: $request->input('label'),
            durationHours: $request->input('duration_hours'),
            durationDays: $request->input('duration_days'),
            isCustom: $request->input('is_custom', false),
            sortOrder: $request->input('sort_order', 0),
        );

        $response = $this->handler->handle($command);

        return new JsonResponse($response->toArray(), Response::HTTP_CREATED);
    }
}
