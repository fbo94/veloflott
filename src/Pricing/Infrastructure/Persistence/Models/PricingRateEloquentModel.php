<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Models;

use Database\Factories\PricingRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @phpstan-use HasFactory<PricingRateFactory>
 */
final class PricingRateEloquentModel extends Model
{
    use HasFactory;
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

    protected static function newFactory(): PricingRateFactory
    {
        return PricingRateFactory::new();
    }
}
