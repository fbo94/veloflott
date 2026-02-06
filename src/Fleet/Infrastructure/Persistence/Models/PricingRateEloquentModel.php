<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PricingRateEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'pricing_rates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'category_id',
        'pricing_class_id',
        'duration_id',
        'price',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];
}
