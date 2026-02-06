<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DurationDefinitionEloquentModel extends Model
{
    use SoftDeletes;

    protected $table = 'duration_definitions';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'duration_hours' => 'integer',
        'duration_days' => 'integer',
        'is_custom' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
