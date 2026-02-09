<?php

declare(strict_types=1);

namespace Fleet\Application\DeleteBikePhoto;

use Fleet\Domain\BikeRepositoryInterface;
use Illuminate\Support\Facades\Storage;

final readonly class DeleteBikePhotoHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(DeleteBikePhotoCommand $command): void
    {
        $bike = $this->bikeRepository->findById($command->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($command->bikeId);
        }

        // Extract file path from URL
        $path = $this->extractPathFromUrl($command->photoUrl);

        // Delete the photo from storage
        if ($path) {
            Storage::disk('bike_photos')->delete($path);
        }

        // Remove photo from bike and save
        $bike->removePhoto($command->photoUrl);
        $this->bikeRepository->save($bike);
    }

    private function extractPathFromUrl(string $url): ?string
    {
        // Extract the path from the full URL
        // Example: http://localhost/storage/bikes/{id}/{filename} -> {id}/{filename}
        $pattern = '/\/storage\/bikes\/(.+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        // If it's already a path without bikes/ prefix, return as is
        // The disk already has 'bikes' in its root
        return null;
    }
}
