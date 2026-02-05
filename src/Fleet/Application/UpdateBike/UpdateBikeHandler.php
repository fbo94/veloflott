<?php

declare(strict_types=1);

namespace Fleet\Application\UpdateBike;

use Fleet\Domain\BikeRepositoryInterface;
use Fleet\Domain\BrakeType;
use Fleet\Domain\FrameSize;
use Fleet\Domain\WheelSize;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class UpdateBikeHandler
{
    public function __construct(
        private BikeRepositoryInterface $bikeRepository,
    ) {
    }

    public function handle(UpdateBikeCommand $command): UpdateBikeResponse
    {
        $bike = $this->bikeRepository->findById($command->bikeId);

        if ($bike === null) {
            throw new BikeNotFoundException($command->bikeId);
        }

        // Parse enum values
        $frameSize = FrameSize::fromRequest(
            unit: $command->frameSizeUnit,
            numericValue: $command->frameSizeNumeric,
            letterValue: $command->frameSizeLetter
        );
        $wheelSize = $command->wheelSize !== null ? WheelSize::from($command->wheelSize) : null;
        $brakeType = $command->brakeType !== null ? BrakeType::from($command->brakeType) : null;
        $purchaseDate = null;
        if ($command->purchaseDate !== null) {
            $purchaseDate = \DateTimeImmutable::createFromFormat('Y-m-d', $command->purchaseDate);
            if ($purchaseDate === false) {
                throw new \InvalidArgumentException('Invalid purchase date format. Expected Y-m-d.');
            }
        }

        try {
            $bike->update(
                modelId: $command->modelId,
                categoryId: $command->categoryId,
                frameSize: $frameSize,
                year: $command->year,
                serialNumber: $command->serialNumber,
                color: $command->color,
                wheelSize: $wheelSize,
                frontSuspension: $command->frontSuspension,
                rearSuspension: $command->rearSuspension,
                brakeType: $brakeType,
                purchasePrice: $command->purchasePrice,
                purchaseDate: $purchaseDate,
                notes: $command->notes,
            );

            // Process photos (upload base64 images and keep existing URLs)
            $processedPhotos = $this->processPhotos($command->photos, $bike->id());
            $bike->updatePhotos($processedPhotos);

            $this->bikeRepository->save($bike);

            return new UpdateBikeResponse(id: $bike->id());
        } catch (\DomainException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Process photos: keep existing URLs and upload base64 images
     *
     * @param string[] $photos
     * @return string[]
     */
    private function processPhotos(array $photos, string $bikeId): array
    {
        $processedPhotos = [];

        foreach ($photos as $photo) {
            // If it's already a URL, keep it
            if ($this->isUrl($photo)) {
                $processedPhotos[] = $photo;
                continue;
            }

            // If it's base64, upload it
            if ($this->isBase64Image($photo)) {
                $uploadedUrl = $this->uploadBase64Image($photo, $bikeId);
                $processedPhotos[] = $uploadedUrl;
                continue;
            }

            // Invalid format, skip
            throw new \InvalidArgumentException('Invalid photo format. Must be a URL or base64 encoded image.');
        }

        return $processedPhotos;
    }

    private function isUrl(string $string): bool
    {
        return str_starts_with($string, 'http://') || str_starts_with($string, 'https://');
    }

    private function isBase64Image(string $string): bool
    {
        // Check if it's a data URI with base64
        if (preg_match('/^data:image\/(\w+);base64,/', $string)) {
            return true;
        }

        // Check if it's raw base64 (without data URI prefix)
        if (base64_decode($string, true) !== false) {
            // Verify it's actually an image by checking magic bytes
            $decoded = base64_decode($string);
            return $this->isImageData($decoded);
        }

        return false;
    }

    private function isImageData(string $data): bool
    {
        // Check for common image file signatures (magic bytes)
        $signatures = [
            "\xFF\xD8\xFF",        // JPEG
            "\x89PNG\r\n\x1a\n",   // PNG
            "GIF87a",              // GIF87a
            "GIF89a",              // GIF89a
            "RIFF",                // WebP (starts with RIFF)
        ];

        foreach ($signatures as $signature) {
            if (str_starts_with($data, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function uploadBase64Image(string $base64String, string $bikeId): string
    {
        // Extract image data and extension from data URI
        if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $base64String, $matches)) {
            $extension = $matches[1];
            $imageData = base64_decode($matches[2]);
        } else {
            // Raw base64 without data URI - default to jpg
            $imageData = base64_decode($base64String);
            $extension = 'jpg';
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $extension;
        $path = $bikeId . '/' . $filename;

        // Store the image
        Storage::disk('bike_photos')->put($path, $imageData);

        // Return the public URL
        return Storage::disk('bike_photos')->url($path);
    }
}
