<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Models;

use Database\Factories\DiscountRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @phpstan-use HasFactory<DiscountRuleFactory>
 */
final class DiscountRuleEloquentModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'discount_rules';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'category_id',
        'pricing_class_id',
        'min_days',
        'min_duration_id',
        'discount_type',
        'discount_value',
        'label',
        'description',
        'is_cumulative',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'min_days' => 'integer',
        'discount_value' => 'float',
        'is_cumulative' => 'boolean',
        'priority' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];

    protected static function newFactory(): DiscountRuleFactory
    {
        return DiscountRuleFactory::new();
    }
}
