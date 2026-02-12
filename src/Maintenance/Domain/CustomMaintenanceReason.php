<?php

declare(strict_types=1);

namespace Maintenance\Domain;

/**
 * Entité CustomMaintenanceReason (Raison de maintenance personnalisée).
 *
 * Permet aux tenants de définir leurs propres raisons de maintenance
 * en complément des raisons prédéfinies dans l'enum MaintenanceReason.
 */
final class CustomMaintenanceReason
{
    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private string $code,
        private string $label,
        private ?string $description,
        private MaintenanceCategory $category,
        private bool $isActive,
        private int $sortOrder = 0,
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

    public function tenantId(): string
    {
        return $this->tenantId;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function category(): MaintenanceCategory
    {
        return $this->category;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Actions =====

    public function updateInformation(
        string $label,
        ?string $description,
        MaintenanceCategory $category,
    ): self {
        $this->label = $label;
        $this->description = $description;
        $this->category = $category;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function updateSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function activate(): self
    {
        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function deactivate(): self
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
