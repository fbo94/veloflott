<?php

declare(strict_types=1);

namespace Fleet\Application\GetModelDetail;

final readonly class GetModelDetailQuery
{
    public function __construct(
        public string $modelId,
    ) {
    }
}
