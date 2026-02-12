<?php

declare(strict_types=1);

namespace Maintenance\Application\CreateCustomMaintenanceReason;

final readonly class CreateCustomMaintenanceReasonResponse
{
    public function __construct(
        public string $id,
        public string $code,
        public string $label,
        public string $message,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'message' => $this->message,
        ];
    }
}
