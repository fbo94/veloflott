<?php

declare(strict_types=1);

namespace Maintenance\Domain;

use DateTimeImmutable;
use Maintenance\Domain\Exceptions\MaintenanceException;

final class Maintenance
{
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
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
    }

    public static function declare(
        string $id,
        string $bikeId,
        MaintenanceType $type,
        MaintenanceReason $reason,
        MaintenancePriority $priority,
        ?string $description = null,
        ?DateTimeImmutable $scheduledAt = null,
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

    // Reconstitution depuis la persistence
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
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}
