<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class PricingClassEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'pricing_classes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'label',
        'description',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];
}
