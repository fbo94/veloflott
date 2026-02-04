<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Customer\Infrastructure\Persistence\Models\CustomerEloquentModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RentalEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'rentals';

    protected $fillable = [
        'id',
        'customer_id',
        'start_date',
        'expected_return_date',
        'actual_return_date',
        'duration',
        'deposit_amount',
        'total_amount',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total_with_tax',
        'status',
        'deposit_status',
        'deposit_retained',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'immutable_datetime',
        'expected_return_date' => 'immutable_datetime',
        'actual_return_date' => 'immutable_datetime',
        'deposit_amount' => 'float',
        'total_amount' => 'float',
        'discount_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'total_with_tax' => 'float',
        'deposit_retained' => 'float',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerEloquentModel::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RentalItemEloquentModel::class, 'rental_id');
    }

    public function equipments(): HasMany
    {
        return $this->hasMany(RentalEquipmentEloquentModel::class, 'rental_id');
    }
}
