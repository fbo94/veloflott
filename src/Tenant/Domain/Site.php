<?php

declare(strict_types=1);

namespace Tenant\Domain;

/**
 * Site - Représente un point de location physique d'un Tenant.
 *
 * Un tenant peut avoir plusieurs sites (ex: Paris 11ème, Paris 15ème).
 * Chaque site peut avoir sa propre configuration (horaires, paramètres).
 */
final class Site
{
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string, array{open: string, close: string}>|null $openingHours
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        private readonly string $id,
        private readonly string $tenantId,
        private string $name,
        private string $slug,
        private ?string $address,
        private ?string $city,
        private ?string $postalCode,
        private string $country,
        private ?string $phone,
        private ?string $email,
        private SiteStatus $status,
        private ?array $openingHours,
        private ?array $settings,
        private ?float $latitude,
        private ?float $longitude,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    // ===== Factory Methods =====

    public static function create(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        ?string $address = null,
        ?string $city = null,
        ?string $postalCode = null,
        string $country = 'FR',
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            name: $name,
            slug: $slug,
            address: $address,
            city: $city,
            postalCode: $postalCode,
            country: $country,
            phone: null,
            email: null,
            status: SiteStatus::ACTIVE,
            openingHours: null,
            settings: null,
            latitude: null,
            longitude: null,
        );
    }

    /**
     * @param array<string, array{open: string, close: string}>|null $openingHours
     * @param array<string, mixed>|null $settings
     */
    public static function reconstitute(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        ?string $address,
        ?string $city,
        ?string $postalCode,
        string $country,
        ?string $phone,
        ?string $email,
        SiteStatus $status,
        ?array $openingHours,
        ?array $settings,
        ?float $latitude,
        ?float $longitude,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            tenantId: $tenantId,
            name: $name,
            slug: $slug,
            address: $address,
            city: $city,
            postalCode: $postalCode,
            country: $country,
            phone: $phone,
            email: $email,
            status: $status,
            openingHours: $openingHours,
            settings: $settings,
            latitude: $latitude,
            longitude: $longitude,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
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

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function postalCode(): ?string
    {
        return $this->postalCode;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function status(): SiteStatus
    {
        return $this->status;
    }

    /**
     * @return array<string, array{open: string, close: string}>|null
     */
    public function openingHours(): ?array
    {
        return $this->openingHours;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function settings(): ?array
    {
        return $this->settings;
    }

    public function latitude(): ?float
    {
        return $this->latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Computed Properties =====

    public function fullAddress(): ?string
    {
        if ($this->address === null) {
            return null;
        }

        $parts = array_filter([
            $this->address,
            trim(($this->postalCode ?? '') . ' ' . ($this->city ?? '')),
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function hasGeolocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function coordinates(): ?array
    {
        if (!$this->hasGeolocation()) {
            return null;
        }

        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    // ===== Status Checks =====

    public function isActive(): bool
    {
        return $this->status === SiteStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === SiteStatus::SUSPENDED;
    }

    public function isClosed(): bool
    {
        return $this->status === SiteStatus::CLOSED;
    }

    public function canAcceptRentals(): bool
    {
        return $this->status->canAcceptRentals();
    }

    // ===== Actions =====

    public function updateInformation(
        string $name,
        ?string $address,
        ?string $city,
        ?string $postalCode,
        ?string $country,
        ?string $phone,
        ?string $email,
    ): self {
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country ?? $this->country;
        $this->phone = $phone;
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function setGeolocation(float $latitude, float $longitude): self
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function clearGeolocation(): self
    {
        $this->latitude = null;
        $this->longitude = null;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param array<string, array{open: string, close: string}> $openingHours
     */
    public function setOpeningHours(array $openingHours): self
    {
        $this->openingHours = $openingHours;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function updateSettings(array $settings): self
    {
        $this->settings = $settings;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function activate(): self
    {
        if ($this->status === SiteStatus::ACTIVE) {
            return $this;
        }

        $this->status = SiteStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function suspend(): self
    {
        if ($this->status === SiteStatus::SUSPENDED) {
            return $this;
        }

        $this->status = SiteStatus::SUSPENDED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function close(): self
    {
        if ($this->status === SiteStatus::CLOSED) {
            return $this;
        }

        $this->status = SiteStatus::CLOSED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    // ===== Validation =====

    public function belongsToTenant(string $tenantId): bool
    {
        return $this->tenantId === $tenantId;
    }
}
