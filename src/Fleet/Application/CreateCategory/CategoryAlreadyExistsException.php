<?php

declare(strict_types=1);

namespace Fleet\Application\CreateCategory;

use Shared\Domain\DomainException;

final class CategoryAlreadyExistsException extends DomainException
{
    protected string $errorCode = 'CATEGORY_ALREADY_EXISTS';

    public function __construct(private readonly string $name)
    {
        parent::__construct("Une catégorie avec le nom '{$name}' existe déjà.");
    }

    public function context(): array
    {
        return ['name' => $this->name];
    }
}
