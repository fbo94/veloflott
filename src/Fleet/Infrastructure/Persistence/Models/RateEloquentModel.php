<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Fleet\Domain\PricingTier;
use Illuminate\Database\Eloquent\Model;

final class RateEloquentModel extends Model
{
    protected $table = 'rates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'category_id',
        'bike_id',
        'pricing_tier',
        'half_day_price',
        'day_price',
        'weekend_price',
        'week_price',
    ];

    protected $casts = [
        'pricing_tier' => PricingTier::class,
        'half_day_price' => 'float',
        'day_price' => 'float',
        'weekend_price' => 'float',
        'week_price' => 'float',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
