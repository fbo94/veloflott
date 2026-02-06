<?php

declare(strict_types=1);

namespace Auth\Domain;

/**
 * Entité User du domaine.
 */
final class User
{
    private ?\DateTimeImmutable $lastLoginAt;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $id,
        private readonly string $keycloakId,
        private string $email,
        private ?string $firstName,
        private ?string $lastName,
        private Role $role,
        private bool $isActive,
        ?\DateTimeImmutable $lastLoginAt = null,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->lastLoginAt = $lastLoginAt;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    // ===== Getters =====

    public function id(): string
    {
        return $this->id;
    }

    public function keycloakId(): string
    {
        return $this->keycloakId;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function firstName(): ?string
    {
        return $this->firstName;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }

    public function fullName(): string
    {
        $name = trim("{$this->firstName} {$this->lastName}");

        return $name !== '' ? $name : $this->email;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function lastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ===== Permissions =====

    public function hasPermission(Permission $permission): bool
    {
        return RolePermissions::has($this->role, $permission);
    }

    public function hasRole(Role $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Retourne toutes les permissions de l'utilisateur.
     *
     * @return Permission[]
     */
    public function permissions(): array
    {
        return RolePermissions::for($this->role);
    }

    // ===== Actions =====

    public function updateFromKeycloak(
        string $email,
        ?string $firstName,
        ?string $lastName,
    ): self {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function changeRole(Role $newRole): self
    {
        $this->role = $newRole;
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

    public function recordLogin(): self
    {
        $this->lastLoginAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }
}
