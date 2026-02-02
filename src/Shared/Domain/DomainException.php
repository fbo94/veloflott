<?php

declare(strict_types=1);

namespace Shared\Domain;

use Exception;

abstract class DomainException extends Exception
{
    protected string $errorCode = 'DOMAIN_ERROR';

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function context(): array
    {
        return [];
    }
}
