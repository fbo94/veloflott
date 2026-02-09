<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tenant\Infrastructure\Persistence\Traits\SiteScoped;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class RentalEquipmentEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;
    use SiteScoped;

    protected $table = 'rental_equipments';

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
        'rental_id',
        'type',
        'quantity',
        'price_per_unit',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_per_unit' => 'float',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(RentalEloquentModel::class, 'rental_id');
    }
}
