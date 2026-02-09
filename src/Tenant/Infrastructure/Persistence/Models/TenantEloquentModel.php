<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string $status
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property array<string, mixed>|null $settings
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 */
final class TenantEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'tenants';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'status',
        'contact_email',
        'contact_phone',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return HasMany<SiteEloquentModel, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(SiteEloquentModel::class, 'tenant_id');
    }

    /**
     * @return HasMany<SiteEloquentModel, $this>
     */
    public function activeSites(): HasMany
    {
        return $this->hasMany(SiteEloquentModel::class, 'tenant_id')
            ->where('status', 'active');
    }
}
