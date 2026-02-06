<?php

declare(strict_types=1);

namespace Fleet\Domain;

final class Model
{
    public function __construct(
        private readonly string $id,
        private string $name,
        private readonly string $brandId,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function brandId(): string
    {
        return $this->brandId;
    }

    public function rename(string $newName): void
    {
        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
