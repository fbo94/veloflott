<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DiscountRuleEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'discount_rules';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'min_days' => 'integer',
        'discount_value' => 'float',
        'is_cumulative' => 'boolean',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];
}
