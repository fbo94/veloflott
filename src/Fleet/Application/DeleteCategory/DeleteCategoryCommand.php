<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteCategory;

final readonly class DeleteCategoryCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
