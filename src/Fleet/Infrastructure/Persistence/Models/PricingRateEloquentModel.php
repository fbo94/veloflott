<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PricingRateEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'pricing_rates';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'category_id',
        'pricing_class_id',
        'duration_id',
        'price',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
    ];
}
