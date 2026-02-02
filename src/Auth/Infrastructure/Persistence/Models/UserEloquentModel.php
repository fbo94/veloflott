<?php

declare(strict_types=1);

namespace Auth\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent Model pour la table users.
 *
 * @property string $id
 * @property string $keycloak_id
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $role
 * @property bool $is_active
 * @property string|null $last_login_at
 * @property string $created_at
 * @property string $updated_at
 */
final class UserEloquentModel extends Model
{
    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'keycloak_id',
        'email',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
