<?php

declare(strict_types=1);

namespace Maintenance\Application\DeleteMaintenancePhoto;

final readonly class DeleteMaintenancePhotoCommand
{
    public function __construct(
        public string $maintenanceId,
        public string $photoUrl,
    ) {}
}
