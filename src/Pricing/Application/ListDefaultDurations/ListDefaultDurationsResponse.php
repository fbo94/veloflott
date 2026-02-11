<?php

declare(strict_types=1);

namespace Pricing\Application\ListDefaultDurations;

/**
 * Response contenant les durées par défaut.
 */
final readonly class ListDefaultDurationsResponse
{
    /**
     * @param array<int, array{
     *     id: string,
     *     code: string,
     *     label: string,
     *     duration_hours: ?int,
     *     duration_days: ?int,
     *     is_custom: bool,
     *     sort_order: int
     * }> $durations
     */
    public function __construct(
        public array $durations,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => $this->durations,
        ];
    }
}
