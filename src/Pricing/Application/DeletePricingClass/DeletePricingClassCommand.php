<?php

declare(strict_types=1);

namespace Pricing\Application\DeletePricingClass;

final readonly class DeletePricingClassCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
