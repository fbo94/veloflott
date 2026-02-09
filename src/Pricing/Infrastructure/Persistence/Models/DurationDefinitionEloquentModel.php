<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Models;

use Database\Factories\DurationDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @phpstan-use HasFactory<DurationDefinitionFactory>
 */
final class DurationDefinitionEloquentModel extends Model
{
    use HasFactory;
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

    protected static function newFactory(): DurationDefinitionFactory
    {
        return DurationDefinitionFactory::new();
    }
}
