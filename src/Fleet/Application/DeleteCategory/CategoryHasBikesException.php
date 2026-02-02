<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteCategory;

use Shared\Domain\DomainException;

final class CategoryHasBikesException extends DomainException
{
    protected string $errorCode = 'CATEGORY_HAS_BIKES';

    public function __construct(
        private readonly string $categoryId,
        private readonly int $bikesCount,
    ) {
        parent::__construct("Impossible de supprimer la catégorie '{$categoryId}' car {$bikesCount} vélo(s) l'utilisent.");
    }

    public function context(): array
    {
        return [
            'category_id' => $this->categoryId,
            'bikes_count' => $this->bikesCount,
        ];
    }
}
