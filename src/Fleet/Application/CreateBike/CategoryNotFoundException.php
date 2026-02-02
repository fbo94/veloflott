<?php

declare(strict_types=1);

namespace Fleet\Application\CreateBike;

use Shared\Domain\DomainException;

final class CategoryNotFoundException extends DomainException
{
    protected string $errorCode = 'CATEGORY_NOT_FOUND';

    public function __construct(private readonly string $categoryId)
    {
        parent::__construct("La catégorie '{$categoryId}' n'existe pas.");
    }

    public function context(): array
    {
        return ['category_id' => $this->categoryId];
    }
}
