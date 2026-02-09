<?php

declare(strict_types=1);

namespace Pricing\Application\DeleteDiscountRule;

final readonly class DeleteDiscountRuleCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
