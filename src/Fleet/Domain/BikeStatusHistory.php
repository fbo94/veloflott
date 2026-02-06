<?php

declare(strict_types=1);

namespace Fleet\Domain;

final class BikeStatusHistory
{
    private \DateTimeImmutable $changedAt;

    public function __construct(
        private readonly string $id,
        private readonly string $bikeId,
        private readonly BikeStatus $oldStatus,
        private readonly BikeStatus $newStatus,
        private readonly ?UnavailabilityReason $unavailabilityReason = null,
        private readonly ?string $unavailabilityComment = null,
        ?\DateTimeImmutable $changedAt = null,
    ) {
        $this->changedAt = $changedAt ?? new \DateTimeImmutable;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function bikeId(): string
    {
        return $this->bikeId;
    }

    public function oldStatus(): BikeStatus
    {
        return $this->oldStatus;
    }

    public function newStatus(): BikeStatus
    {
        return $this->newStatus;
    }

    public function unavailabilityReason(): ?UnavailabilityReason
    {
        return $this->unavailabilityReason;
    }

    public function unavailabilityComment(): ?string
    {
        return $this->unavailabilityComment;
    }

    public function changedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }
}
