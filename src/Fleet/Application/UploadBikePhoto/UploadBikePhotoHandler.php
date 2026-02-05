<?php

declare(strict_types=1);

namespace Fleet\Application\UploadBikePhoto;

use Fleet\Domain\BikeRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class UploadBikePhotoHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(UploadBikePhotoCommand $command): UploadBikePhotoResponse
    {
        $bike = $this->bikeRepository->findById($command->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($command->bikeId);
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $command->photo->getClientOriginalExtension();

        // Store the photo in bike_photos disk (local in dev, GCS in prod)
        $path = Storage::disk('bike_photos')->putFileAs(
            $command->bikeId,
            $command->photo,
            $filename
        );

        // Get the URL for the stored photo
        $photoUrl = Storage::disk('bike_photos')->url($path);

        // Add photo to bike and save
        $bike->addPhoto($photoUrl);
        $this->bikeRepository->save($bike);

        return new UploadBikePhotoResponse(
            bikeId: $bike->id(),
            photoUrl: $photoUrl,
        );
    }
}
