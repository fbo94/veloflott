<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Persistence;

use Auth\Domain\Role;
use Auth\Domain\User;
use Auth\Domain\UserRepositoryInterface;
use Auth\Infrastructure\Persistence\Models\UserEloquentModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserEloquentModel::find($id);

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByKeycloakId(string $keycloakId): ?User
    {
        $model = UserEloquentModel::where('keycloak_id', $keycloakId)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserEloquentModel::where('email', $email)->first();

        return $model !== null ? $this->toDomain($model) : null;
    }

    /**
     * @return User[]
     */
    public function findAll(?Role $role = null, ?bool $isActive = null): array
    {
        $query = UserEloquentModel::query();

        if ($role !== null) {
            $query->where('role', $role->value);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->get()->map(fn ($model) => $this->toDomain($model))->all();
    }

    public function save(User $user): void
    {
        UserEloquentModel::updateOrCreate(
            ['id' => $user->id()],
            [
                'keycloak_id' => $user->keycloakId(),
                'tenant_id' => $user->tenantId(),
                'email' => $user->email(),
                'first_name' => $user->firstName(),
                'last_name' => $user->lastName(),
                'role' => $user->role()->value,
                'is_active' => $user->isActive(),
                'last_login_at' => $user->lastLoginAt(),
            ]
        );
    }

    /**
     * @throws \Exception
     */
    private function toDomain(UserEloquentModel $model): User
    {
        return new User(
            id: $model->id,
            keycloakId: $model->keycloak_id,
            tenantId: $model->tenant_id,
            email: $model->email,
            firstName: $model->first_name,
            lastName: $model->last_name,
            role: Role::from($model->role),
            isActive: $model->is_active,
            lastLoginAt: $model->last_login_at !== null ? \DateTimeImmutable::createFromInterface($model->last_login_at) : null,
            createdAt: \DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromInterface($model->updated_at) : null,
        );
    }
}
