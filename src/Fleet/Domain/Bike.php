<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Entité Bike du domaine - Agrégat Root.
 */
final class Bike
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    /**
     * @param  string[]  $photos
     */
    public function __construct(
        private readonly string $id,
        private readonly string $qrCodeUuid,
        private readonly string $internalNumber,
        private string $modelId,
        private string $categoryId,
        private FrameSize $frameSize,
        private BikeStatus $status,
        private PricingTier $pricingTier,
        private ?string $pricingClassId,
        private ?int $year,
        private ?string $serialNumber,
        private ?string $color,
        private ?WheelSize $wheelSize,
        private ?int $frontSuspension,
        private ?int $rearSuspension,
        private ?BrakeType $brakeType,
        private ?float $purchasePrice,
        private ?\DateTimeImmutable $purchaseDate,
        private ?string $notes,
        private array $photos,
        private ?RetirementReason $retirementReason = null,
        private ?string $retirementComment = null,
        private ?\DateTimeImmutable $retiredAt = null,
        private ?UnavailabilityReason $unavailabilityReason = null,
        private ?string $unavailabilityComment = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function qrCodeUuid(): string
    {
        return $this->qrCodeUuid;
    }

    public function internalNumber(): string
    {
        return $this->internalNumber;
    }

    public function modelId(): string
    {
        return $this->modelId;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function frameSize(): FrameSize
    {
        return $this->frameSize;
    }

    public function status(): BikeStatus
    {
        return $this->status;
    }

    public function pricingTier(): PricingTier
    {
        return $this->pricingTier;
    }

    public function pricingClassId(): ?string
    {
        return $this->pricingClassId;
    }

    public function year(): ?int
    {
        return $this->year;
    }

    public function serialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function color(): ?string
    {
        return $this->color;
    }

    public function wheelSize(): ?WheelSize
    {
        return $this->wheelSize;
    }

    public function frontSuspension(): ?int
    {
        return $this->frontSuspension;
    }

    public function rearSuspension(): ?int
    {
        return $this->rearSuspension;
    }

    public function brakeType(): ?BrakeType
    {
        return $this->brakeType;
    }

    public function purchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function purchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return string[]
     */
    public function photos(): array
    {
        return $this->photos;
    }

    public function retirementReason(): ?RetirementReason
    {
        return $this->retirementReason;
    }

    public function retirementComment(): ?string
    {
        return $this->retirementComment;
    }

    public function retiredAt(): ?\DateTimeImmutable
    {
        return $this->retiredAt;
    }

    public function unavailabilityReason(): ?UnavailabilityReason
    {
        return $this->unavailabilityReason;
    }

    public function unavailabilityComment(): ?string
    {
        return $this->unavailabilityComment;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Business Logic =====

    public function isRentable(): bool
    {
        return $this->status->isRentable() && ! $this->isRetired();
    }

    public function isRetired(): bool
    {
        return $this->status === BikeStatus::RETIRED;
    }

    public function canBeModified(): bool
    {
        return $this->status->canBeModified() && ! $this->isRetired();
    }

    public function canBeRetired(): bool
    {
        return $this->status !== BikeStatus::RENTED;
    }

    // ===== Actions =====

    public function update(
        string $modelId,
        string $categoryId,
        FrameSize $frameSize,
        ?int $year,
        ?string $serialNumber,
        ?string $color,
        ?WheelSize $wheelSize,
        ?int $frontSuspension,
        ?int $rearSuspension,
        ?BrakeType $brakeType,
        ?float $purchasePrice,
        ?\DateTimeImmutable $purchaseDate,
        ?string $notes,
    ): self {
        if (! $this->canBeModified()) {
            throw new \DomainException('Cannot modify this bike in its current status');
        }

        $this->modelId = $modelId;
        $this->categoryId = $categoryId;
        $this->frameSize = $frameSize;
        $this->year = $year;
        $this->serialNumber = $serialNumber;
        $this->color = $color;
        $this->wheelSize = $wheelSize;
        $this->frontSuspension = $frontSuspension;
        $this->rearSuspension = $rearSuspension;
        $this->brakeType = $brakeType;
        $this->purchasePrice = $purchasePrice;
        $this->purchaseDate = $purchaseDate;
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function addPhoto(string $photoPath): self
    {
        $this->photos[] = $photoPath;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function removePhoto(string $photoPath): self
    {
        $this->photos = array_values(array_filter(
            $this->photos,
            fn ($photo) => $photo !== $photoPath
        ));
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param  string[]  $photos
     */
    public function updatePhotos(array $photos): self
    {
        $this->photos = $photos;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function changeStatus(BikeStatus $newStatus): self
    {
        if ($this->status === BikeStatus::RENTED) {
            throw new \DomainException('Cannot manually change status of a rented bike');
        }

        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function changePricingTier(PricingTier $newTier): self
    {
        $this->pricingTier = $newTier;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markAsRented(): self
    {
        if (! $this->isRentable()) {
            throw new \DomainException('Bike is not rentable');
        }

        $this->status = BikeStatus::RENTED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markAsReturned(): self
    {
        if ($this->status !== BikeStatus::RENTED) {
            throw new \DomainException('Bike is not currently rented');
        }

        $this->status = BikeStatus::AVAILABLE;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markAsAvailable(): self
    {
        if ($this->status->isRentable()) {
            throw new \DomainException('Bike is already available');
        }

        $this->status = BikeStatus::AVAILABLE;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function changeStatusWithReason(
        BikeStatus $newStatus,
        ?UnavailabilityReason $unavailabilityReason = null,
        ?string $unavailabilityComment = null
    ): self {
        // Cannot manually change status of rented bike
        if ($this->status === BikeStatus::RENTED) {
            throw new \DomainException('Cannot manually change status of a rented bike');
        }

        // Cannot manually set status to rented
        if ($newStatus === BikeStatus::RENTED) {
            throw new \DomainException('Cannot manually set bike status to rented');
        }

        // Cannot manually set status to retired (use retire() method)
        if ($newStatus === BikeStatus::RETIRED) {
            throw new \DomainException('Use retire() method to retire a bike');
        }

        // Unavailability reason is required when marking bike as unavailable
        if ($newStatus === BikeStatus::UNAVAILABLE && $unavailabilityReason === null) {
            throw new \DomainException('Unavailability reason is required when marking bike as unavailable');
        }

        // Set new status
        $this->status = $newStatus;

        // Handle unavailability reason
        if ($newStatus === BikeStatus::UNAVAILABLE) {
            $this->unavailabilityReason = $unavailabilityReason;
            $this->unavailabilityComment = $unavailabilityComment;
        } else {
            // Clear unavailability reason when changing to other status
            $this->unavailabilityReason = null;
            $this->unavailabilityComment = null;
        }

        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function retire(RetirementReason $reason, ?string $comment = null): self
    {
        if (! $this->canBeRetired()) {
            throw new \DomainException('Cannot retire a bike that is currently rented');
        }

        $this->status = BikeStatus::RETIRED;
        $this->retirementReason = $reason;
        $this->retirementComment = $comment;
        $this->retiredAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
