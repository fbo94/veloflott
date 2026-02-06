<?php

declare(strict_types=1);

namespace Maintenance\Application\UploadMaintenancePhoto;

final readonly class UploadMaintenancePhotoResponse
{
    public function __construct(
        public string $maintenanceId,
        public string $photoUrl,
    ) {}

    public function toArray(): array
    {
        return [
            'maintenance_id' => $this->maintenanceId,
            'photo_url' => $this->photoUrl,
        ];
    }
}
