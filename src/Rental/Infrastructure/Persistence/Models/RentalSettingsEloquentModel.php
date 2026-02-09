<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class RentalSettingsEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'rental_settings';

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
        'late_tolerance_minutes',
        'hourly_late_rate',
        'daily_late_rate',
        'early_return_enabled',
        'early_return_fee_type',
        'early_return_fee_percentage',
        'early_return_fee_fixed',
        'max_rental_duration_days',
        'min_reservation_hours_ahead',
    ];

    protected $casts = [
        'late_tolerance_minutes' => 'integer',
        'hourly_late_rate' => 'float',
        'daily_late_rate' => 'float',
        'early_return_enabled' => 'boolean',
        'early_return_fee_percentage' => 'float',
        'early_return_fee_fixed' => 'float',
        'max_rental_duration_days' => 'integer',
        'min_reservation_hours_ahead' => 'integer',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
