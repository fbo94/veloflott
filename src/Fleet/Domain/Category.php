<?php

declare(strict_types=1);

namespace Fleet\Domain;

/**
 * Entité Category du domaine.
 */
final class Category
{
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private string $name,
        private ?string $slug,
        private ?string $description,
        private bool $isDefault,
        private int $displayOrder,
        private ?string $parentId = null,
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

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): ?string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function displayOrder(): int
    {
        return $this->displayOrder;
    }

    public function parentId(): ?string
    {
        return $this->parentId;
    }

    public function isMainCategory(): bool
    {
        return $this->parentId === null;
    }

    public function isSubCategory(): bool
    {
        return $this->parentId !== null;
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

    public function update(string $name, ?string $slug, ?string $description): self
    {
        if ($this->isDefault) {
            throw new \DomainException('Cannot modify a default category');
        }

        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function changeOrder(int $order): self
    {
        $this->displayOrder = $order;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function canBeDeleted(): bool
    {
        return !$this->isDefault;
    }
}
