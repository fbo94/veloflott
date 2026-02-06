<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTopBikes;

final readonly class GetTopBikesQuery
{
    public function __construct(
        public int $limit = 10,
    ) {}
}
