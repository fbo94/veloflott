<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTopBikes;

final readonly class GetTopBikesResponse
{
    /**
     * @param  array<int, array{bike_id: string, internal_number: string, rental_count: int, total_revenue_cents: int, total_revenue_formatted: string}>  $topBikes
     */
    public function __construct(
        public array $topBikes,
        public int $limit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'top_bikes' => $this->topBikes,
            'limit' => $this->limit,
        ];
    }
}
