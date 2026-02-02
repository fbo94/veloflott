<?php

declare(strict_types=1);

namespace Auth\Domain;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByKeycloakId(string $keycloakId): ?User;

    public function findByEmail(string $email): ?User;

    /**
     * @return User[]
     */
    public function findAll(?Role $role = null, ?bool $isActive = null): array;

    public function save(User $user): void;
}
