<?php

declare(strict_types=1);

namespace Auth\Application\ListUsers;

use Auth\Domain\User;

final readonly class ListUsersResponse
{
    /**
     * @param array<int, array<string, mixed>> $users
     */
    public function __construct(
        public array $users,
        public int $total,
    ) {
    }

    /**
     * @param User[] $users
     */
    public static function fromUsers(array $users): self
    {
        $data = array_map(
            fn (User $user) => [
                'id' => $user->id(),
                'email' => $user->email(),
                'first_name' => $user->firstName(),
                'last_name' => $user->lastName(),
                'full_name' => $user->fullName(),
                'role' => $user->role()->value,
                'role_label' => $user->role()->label(),
                'is_active' => $user->isActive(),
                'last_login_at' => $user->lastLoginAt()?->format('c'),
            ],
            $users
        );

        return new self(
            users: $data,
            total: count($data),
        );
    }

    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'total' => $this->total,
        ];
    }
}
