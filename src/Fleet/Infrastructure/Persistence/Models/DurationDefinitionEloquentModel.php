<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DurationDefinitionEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'duration_definitions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'code',
        'label',
        'duration_hours',
        'duration_days',
        'is_custom',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'duration_days' => 'integer',
        'is_custom' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];
}
