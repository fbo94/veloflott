<?php

declare(strict_types=1);

namespace Maintenance\Application\DeclareMaintenance;

use DateTimeImmutable;

final readonly class DeclareMaintenanceCommand
{
    /**
     * @param  array<int, string>  $photos
     */
    public function __construct(
        public string $bikeId,
        public string $type, // 'preventive' | 'curative'
        public string $reason, // 'full_service' | 'brake_bleeding' | 'suspension' | 'wheels' | 'other'
        public string $priority, // 'normal' | 'urgent'
        public ?string $description = null,
        public ?DateTimeImmutable $scheduledAt = null,
        public array $photos = [],
    ) {}
}
