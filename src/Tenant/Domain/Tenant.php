<?php

declare(strict_types=1);

namespace Tenant\Domain;

/**
 * Entité Tenant (Organisation/Entreprise).
 *
 * Représente une organisation dans le système multi-tenant.
 * Chaque tenant a ses propres données isolées.
 */
final class Tenant
{
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<string, mixed>|null $settings
     */
    public function __construct(
        private readonly string $id,
        private string $name,
        private string $slug,
        private ?string $domain,
        private TenantStatus $status,
        private ?string $contactEmail,
        private ?string $contactPhone,
        private ?array $settings,
        private ?string $address = null,
        private ?string $logoUrl = null,
        private ?string $subscriptionPlanId = null,
        private int $maxUsers = 5,
        private int $maxBikes = 50,
        private int $maxSites = 1,
        private ?\DateTimeImmutable $trialEndsAt = null,
        private bool $onboardingCompleted = false,
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

    public function slug(): string
    {
        return $this->slug;
    }

    public function domain(): ?string
    {
        return $this->domain;
    }

    public function status(): TenantStatus
    {
        return $this->status;
    }

    public function contactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function contactPhone(): ?string
    {
        return $this->contactPhone;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function settings(): ?array
    {
        return $this->settings;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function logoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function subscriptionPlanId(): ?string
    {
        return $this->subscriptionPlanId;
    }

    public function maxUsers(): int
    {
        return $this->maxUsers;
    }

    public function maxBikes(): int
    {
        return $this->maxBikes;
    }

    public function maxSites(): int
    {
        return $this->maxSites;
    }

    public function trialEndsAt(): ?\DateTimeImmutable
    {
        return $this->trialEndsAt;
    }

    public function onboardingCompleted(): bool
    {
        return $this->onboardingCompleted;
    }

    public function isInTrial(): bool
    {
        if ($this->trialEndsAt === null) {
            return false;
        }

        return $this->trialEndsAt > new \DateTimeImmutable();
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Status checks =====

    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    // ===== Actions =====

    public function updateInformation(
        string $name,
        ?string $contactEmail,
        ?string $contactPhone,
    ): self {
        $this->name = $name;
        $this->contactEmail = $contactEmail;
        $this->contactPhone = $contactPhone;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    /**
     * @param array<string, mixed>|null $settings
     */
    public function updateSettings(?array $settings): self
    {
        $this->settings = $settings;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function activate(): self
    {
        $this->status = TenantStatus::ACTIVE;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function suspend(): self
    {
        $this->status = TenantStatus::SUSPENDED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function archive(): self
    {
        $this->status = TenantStatus::ARCHIVED;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
