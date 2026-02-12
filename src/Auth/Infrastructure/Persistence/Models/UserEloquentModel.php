<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model pour la table users.
 *
 * @property string $id
 * @property string $keycloak_id
 * @property string|null $tenant_id
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $role
 * @property bool $is_active
 * @property \DateTimeImmutable|null $last_login_at
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable|null $updated_at
 */
final class UserEloquentModel extends Model
{
    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'keycloak_id',
        'tenant_id',
        'email',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
