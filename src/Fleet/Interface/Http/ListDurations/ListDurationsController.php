<?php

declare(strict_types=1);

namespace Fleet\Interface\Http\ListDurations;

use Fleet\Domain\DurationDefinitionRepositoryInterface;
use Illuminate\Http\JsonResponse;

final class ListDurationsController
{
    public function __construct(
        private readonly DurationDefinitionRepositoryInterface $repository,
    ) {}

    public function __invoke(): JsonResponse
    {
        $durations = $this->repository->findAllActive();

        return new JsonResponse([
            'data' => array_map(
                fn ($duration) => [
                    'id' => $duration->id(),
                    'code' => $duration->code(),
                    'label' => $duration->label(),
                    'duration_hours' => $duration->durationHours(),
                    'duration_days' => $duration->durationDays(),
                    'is_custom' => $duration->isCustom(),
                    'sort_order' => $duration->sortOrder(),
                    'is_active' => $duration->isActive(),
                ],
                $durations
            ),
        ]);
    }
}
