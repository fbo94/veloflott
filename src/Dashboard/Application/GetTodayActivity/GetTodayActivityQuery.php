<?php

declare(strict_types=1);

namespace Dashboard\Application\GetTodayActivity;

final readonly class GetTodayActivityQuery
{
    public function __construct(
        public ?\DateTimeImmutable $date = null,
    ) {}
}
