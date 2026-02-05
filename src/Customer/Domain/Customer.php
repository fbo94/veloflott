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
        private ?string $identityDocumentType,
        private ?string $identityDocumentNumber,
        private ?int $height,
        private ?int $weight,
        private ?string $address,
        private ?string $notes,
        private array $photos = [],
        private bool $isRisky = false,
        private readonly \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

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

    public function identityDocumentType(): ?string
    {
        return $this->identityDocumentType;
    }

    public function identityDocumentNumber(): ?string
    {
        return $this->identityDocumentNumber;
    }

    public function height(): ?int
    {
        return $this->height;
    }

    public function weight(): ?int
    {
        return $this->weight;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function isRisky(): bool
    {
        return $this->isRisky;
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
        if (!in_array($photoUrl, $this->photos, true)) {
            $this->photos[] = $photoUrl;
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function removePhoto(string $photoUrl): self
    {
        $this->photos = array_values(array_filter(
            $this->photos,
            fn (string $url) => $url !== $photoUrl
        ));
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
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
        ?string $identityDocumentType,
        ?string $identityDocumentNumber,
        ?int $height,
        ?int $weight,
        ?string $address,
        ?string $notes,
        array $photos = [],
    ): self {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->identityDocumentType = $identityDocumentType;
        $this->identityDocumentNumber = $identityDocumentNumber;
        $this->height = $height;
        $this->weight = $weight;
        $this->address = $address;
        $this->notes = $notes;
        $this->photos = $photos;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markAsRisky(): void
    {
        $this->isRisky = true;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unmarkAsRisky(): void
    {
        $this->isRisky = false;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
