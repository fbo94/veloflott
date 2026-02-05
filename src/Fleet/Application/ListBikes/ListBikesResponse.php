<?php

declare(strict_types=1);

namespace Fleet\Application\ListBikes;

final readonly class ListBikesResponse
{
    /**
     * @param BikeDto[] $bikes
     */
    public function __construct(
        public array $bikes,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn (BikeDto $bike) => $bike->toArray(), $this->bikes),
            'pagination' => [
                'total' => $this->total,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'total_pages' => (int) ceil($this->total / $this->perPage),
            ],
        ];
    }
}
