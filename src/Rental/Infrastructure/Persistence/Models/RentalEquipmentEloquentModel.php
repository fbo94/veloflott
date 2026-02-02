<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RentalEquipmentEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'rental_equipments';

    protected $fillable = [
        'rental_id',
        'type',
        'quantity',
        'price_per_unit',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_per_unit' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(RentalEloquentModel::class, 'rental_id');
    }
}
