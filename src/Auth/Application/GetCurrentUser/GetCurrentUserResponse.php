<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

use Auth\Domain\User;

final readonly class GetCurrentUserResponse
{
    public function __construct(
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
        public string $fullName,
        public string $role,
        public string $roleLabel,
        public bool $isActive,
        public array $permissions,
        public ?string $lastLoginAt,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id(),
            email: $user->email(),
            firstName: $user->firstName(),
            lastName: $user->lastName(),
            fullName: $user->fullName(),
            role: $user->role()->value,
            roleLabel: $user->role()->label(),
            isActive: $user->isActive(),
            permissions: array_map(
                fn ($permission) => $permission->value,
                $user->permissions()
            ),
            lastLoginAt: $user->lastLoginAt()?->format('c'),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->fullName,
            'role' => $this->role,
            'role_label' => $this->roleLabel,
            'is_active' => $this->isActive,
            'permissions' => $this->permissions,
            'last_login_at' => $this->lastLoginAt,
        ];
    }
}
