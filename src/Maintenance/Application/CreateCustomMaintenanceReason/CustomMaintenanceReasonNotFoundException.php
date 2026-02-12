<?php

declare(strict_types=1);

namespace Maintenance\Application\CreateCustomMaintenanceReason;

use Maintenance\Domain\Exceptions\MaintenanceException;

final class CustomMaintenanceReasonNotFoundException extends MaintenanceException
{
    public static function withId(string $id): self
    {
        return new self("Custom maintenance reason with id '{$id}' not found.");
    }

    public static function withCode(string $code): self
    {
        return new self("Custom maintenance reason with code '{$code}' not found.");
    }
}
