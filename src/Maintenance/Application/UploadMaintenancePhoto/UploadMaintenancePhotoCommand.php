<?php

declare(strict_types=1);

namespace Maintenance\Application\UploadMaintenancePhoto;

use Illuminate\Http\UploadedFile;

final readonly class UploadMaintenancePhotoCommand
{
    public function __construct(
        public string $maintenanceId,
        public UploadedFile $photo,
    ) {
    }
}
