<?php

declare(strict_types=1);

namespace Fleet\Application\ListBikes;

final readonly class ListBikesQuery
{
    /**
     * @param string[] $statuses
     * @param string[] $categoryIds
     * @param string[] $frameSizes
     */
    public function __construct(
        public ?array $statuses = null,
        public ?array $categoryIds = null,
        public ?array $frameSizes = null,
        public bool $includeRetired = false,
        public ?string $search = null,
        public string $sortBy = 'internal_number',
        public string $sortDirection = 'asc',
        public int $page = 1,
        public int $perPage = 50,
    ) {
    }
}
