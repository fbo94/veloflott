<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Fleet\Domain\PricingTier;
use Illuminate\Database\Eloquent\Model;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class RateEloquentModel extends Model
{
    use TenantScoped;

    protected $table = 'rates';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
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
