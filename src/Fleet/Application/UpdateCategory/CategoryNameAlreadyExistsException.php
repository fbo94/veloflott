<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateCategory;

use Shared\Domain\DomainException;

final class CategoryNameAlreadyExistsException extends DomainException
{
    protected string $errorCode = 'CATEGORY_NAME_ALREADY_EXISTS';

    public function __construct(private readonly string $name)
    {
        parent::__construct("Une catégorie avec le nom '{$name}' existe déjà.");
    }

    public function context(): array
    {
        return ['name' => $this->name];
    }
}
