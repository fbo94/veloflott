<?php

declare(strict_types=1);

namespace Customer\Domain;

final class Customer
{
    public function __construct(
        private readonly string $id,
        private string $firstName,
        private string $lastName,
        private ?string $email,
        private ?string $phone,
        private ?string $notes,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function update(
        string $firstName,
        string $lastName,
        ?string $email,
        ?string $phone,
        ?string $notes,
    ): self {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->notes = $notes;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
