<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Maintenance\Domain\MaintenanceCategory;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $code
 * @property string $label
 * @property string|null $description
 * @property string $category
 * @property bool $is_active
 * @property int $sort_order
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 */
final class CustomMaintenanceReasonEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;

    protected $table = 'custom_maintenance_reasons';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'code',
        'label',
        'description',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'category' => MaintenanceCategory::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
