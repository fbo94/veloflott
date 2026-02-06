<?php

declare(strict_types=1);

namespace Maintenance\Application\UploadMaintenancePhoto;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\MaintenanceRepositoryInterface;

final readonly class UploadMaintenancePhotoHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {
    }

    /**
     * @throws MaintenanceNotFoundException
     * @throws MaintenanceException
     */
    public function handle(UploadMaintenancePhotoCommand $command): UploadMaintenancePhotoResponse
    {
        try {
            $maintenance = $this->maintenanceRepository->findById($command->maintenanceId);
        } catch (MaintenanceException $e) {
            throw new MaintenanceNotFoundException($command->maintenanceId);
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $command->photo->getClientOriginalExtension();

        // Store the photo in maintenance_photos disk (local in dev, GCS in prod)
        $path = Storage::disk('maintenance_photos')->putFileAs(
            $command->maintenanceId,
            $command->photo,
            $filename
        );

        if ($path === false) {
            throw new \RuntimeException('Failed to store maintenance photo');
        }

        // Get the URL for the stored photo
        $photoUrl = Storage::disk('maintenance_photos')->url($path);

        // Add photo to maintenance and save
        $maintenance->addPhoto($photoUrl);
        $this->maintenanceRepository->save($maintenance);

        return new UploadMaintenancePhotoResponse(
            maintenanceId: $maintenance->id(),
            photoUrl: $photoUrl,
        );
    }
}
