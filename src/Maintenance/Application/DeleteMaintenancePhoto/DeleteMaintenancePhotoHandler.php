<?php

declare(strict_types=1);

namespace Maintenance\Application\DeleteMaintenancePhoto;

use Maintenance\Domain\Exceptions\MaintenanceException;
use Maintenance\Domain\MaintenanceRepositoryInterface;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteMaintenancePhotoHandler
{
    public function __construct(
        private MaintenanceRepositoryInterface $maintenanceRepository,
    ) {
    }

    /**
     * @throws MaintenanceNotFoundException
     * @throws MaintenanceException
     */
    public function handle(DeleteMaintenancePhotoCommand $command): void
    {
        try {
            $maintenance = $this->maintenanceRepository->findById($command->maintenanceId);
        } catch (MaintenanceException $e) {
            throw new MaintenanceNotFoundException($command->maintenanceId);
        }

        // Extract file path from URL
        $path = $this->extractPathFromUrl($command->photoUrl);

        // Delete the photo from storage
        if ($path) {
            Storage::disk('maintenance_photos')->delete($path);
        }

        // Remove photo from maintenance and save
        $maintenance->removePhoto($command->photoUrl);
        $this->maintenanceRepository->save($maintenance);
    }

    private function extractPathFromUrl(string $url): ?string
    {
        // Extract the path from the full URL
        // Example: http://localhost/storage/maintenances/{id}/{filename} -> {id}/{filename}
        $pattern = '/\/storage\/maintenances\/(.+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        // If it's already a path without maintenances/ prefix, return as is
        // The disk already has 'maintenances' in its root
        return null;
    }
}
