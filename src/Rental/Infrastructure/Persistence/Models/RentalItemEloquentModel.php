<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tenant\Infrastructure\Persistence\Traits\SiteScoped;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class RentalItemEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;
    use SiteScoped;

    protected $table = 'rental_items';

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
        'rental_id',
        'bike_id',
        'daily_rate',
        'quantity',
        'client_height',
        'client_weight',
        'saddle_height',
        'front_suspension_pressure',
        'rear_suspension_pressure',
        'pedal_type',
        'check_in_notes',
        'return_condition',
        'damage_description',
        'damage_photos',
    ];

    protected $casts = [
        'daily_rate' => 'float',
        'quantity' => 'integer',
        'client_height' => 'integer',
        'client_weight' => 'integer',
        'saddle_height' => 'integer',
        'front_suspension_pressure' => 'integer',
        'rear_suspension_pressure' => 'integer',
        'damage_photos' => 'array',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(RentalEloquentModel::class, 'rental_id');
    }

    public function bike(): BelongsTo
    {
        return $this->belongsTo(BikeEloquentModel::class, 'bike_id');
    }
}
