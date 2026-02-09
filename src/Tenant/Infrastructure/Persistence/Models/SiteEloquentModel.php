<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $slug
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postal_code
 * @property string $country
 * @property string|null $phone
 * @property string|null $email
 * @property string $status
 * @property array<string, array{open: string, close: string}>|null $opening_hours
 * @property array<string, mixed>|null $settings
 * @property float|null $latitude
 * @property float|null $longitude
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 */
final class SiteEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'sites';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'slug',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'status',
        'opening_hours',
        'settings',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'settings' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<TenantEloquentModel, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantEloquentModel::class, 'tenant_id');
    }
}
