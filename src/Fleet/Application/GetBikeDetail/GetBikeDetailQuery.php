<?php

declare(strict_types=1);

namespace Fleet\Application\GetBikeDetail;

final readonly class GetBikeDetailQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
