<?php

declare(strict_types=1);

namespace Maintenance\Domain;

use DateTimeImmutable;
use Maintenance\Domain\Exceptions\MaintenanceException;

final class Maintenance
{
    /**
     * @param array<int, string> $photos
     */
    private function __construct(
        private readonly string $id,
        private readonly string $bikeId,
        private readonly MaintenanceType $type,
        private readonly MaintenanceReason $reason,
        private readonly MaintenancePriority $priority,
        private MaintenanceStatus $status,
        private ?string $description,
        private readonly DateTimeImmutable $scheduledAt,
        private ?DateTimeImmutable $startedAt,
        private ?DateTimeImmutable $completedAt,
        private ?string $workDescription,
        private ?string $partsReplaced,
        private ?int $cost, // En centimes
        private array $photos,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<int, string> $photos
     */
    public static function declare(
        string $id,
        string $bikeId,
        MaintenanceType $type,
        MaintenanceReason $reason,
        MaintenancePriority $priority,
        ?string $description = null,
        ?DateTimeImmutable $scheduledAt = null,
        array $photos = [],
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            bikeId: $bikeId,
            type: $type,
            reason: $reason,
            priority: $priority,
            status: MaintenanceStatus::TODO,
            description: $description,
            scheduledAt: $scheduledAt ?? $now,
            startedAt: null,
            completedAt: null,
            workDescription: null,
            partsReplaced: null,
            cost: null,
            photos: $photos,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    /**
     * @throws MaintenanceException
     */
    public function start(): void
    {
        if (!$this->status->canBeStarted()) {
            throw MaintenanceException::cannotStart($this->id, "Current status is {$this->status->value}");
        }

        $this->status = MaintenanceStatus::IN_PROGRESS;
        $this->startedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws MaintenanceException
     */
    public function complete(
        string $workDescription,
        ?string $partsReplaced = null,
        ?int $cost = null,
    ): void {
        if (!$this->status->canBeCompleted()) {
            throw MaintenanceException::cannotComplete($this->id, "Current status is {$this->status->value}");
        }

        $this->status = MaintenanceStatus::COMPLETED;
        $this->completedAt = new DateTimeImmutable();
        $this->workDescription = $workDescription;
        $this->partsReplaced = $partsReplaced;
        $this->cost = $cost;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @throws MaintenanceException
     */
    public function updateDescription(string $description): void
    {
        if ($this->status->isCompleted()) {
            throw MaintenanceException::cannotModify($this->id, 'Maintenance is completed');
        }

        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    // Getters
    public function id(): string
    {
        return $this->id;
    }

    public function bikeId(): string
    {
        return $this->bikeId;
    }

    public function type(): MaintenanceType
    {
        return $this->type;
    }

    public function reason(): MaintenanceReason
    {
        return $this->reason;
    }

    public function priority(): MaintenancePriority
    {
        return $this->priority;
    }

    public function status(): MaintenanceStatus
    {
        return $this->status;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function scheduledAt(): DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function startedAt(): ?DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function workDescription(): ?string
    {
        return $this->workDescription;
    }

    public function partsReplaced(): ?string
    {
        return $this->partsReplaced;
    }

    public function cost(): ?int
    {
        return $this->cost;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return array<int, string>
     */
    public function photos(): array
    {
        return $this->photos;
    }

    public function addPhoto(string $photoUrl): self
    {
        $this->photos[] = $photoUrl;
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    public function removePhoto(string $photoUrl): self
    {
        $this->photos = array_values(
            array_filter($this->photos, fn(string $photo) => $photo !== $photoUrl)
        );
        $this->updatedAt = new DateTimeImmutable();

        return $this;
    }

    // Reconstitution depuis la persistence
    /**
     * @param array<int, string> $photos
     */
    public static function reconstitute(
        string $id,
        string $bikeId,
        MaintenanceType $type,
        MaintenanceReason $reason,
        MaintenancePriority $priority,
        MaintenanceStatus $status,
        ?string $description,
        DateTimeImmutable $scheduledAt,
        ?DateTimeImmutable $startedAt,
        ?DateTimeImmutable $completedAt,
        ?string $workDescription,
        ?string $partsReplaced,
        ?int $cost,
        array $photos,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            bikeId: $bikeId,
            type: $type,
            reason: $reason,
            priority: $priority,
            status: $status,
            description: $description,
            scheduledAt: $scheduledAt,
            startedAt: $startedAt,
            completedAt: $completedAt,
            workDescription: $workDescription,
            partsReplaced: $partsReplaced,
            cost: $cost,
            photos: $photos,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
