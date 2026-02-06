<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Fleet\Domain\PricingTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BikeEloquentModel extends Model
{
    protected $table = 'bikes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'qr_code_uuid',
        'internal_number',
        'model_id',
        'category_id',
        'frame_size_unit',
        'frame_size_numeric',
        'frame_size_letter',
        'frame_size_letter_equivalent',
        'status',
        'pricing_tier',
        'year',
        'serial_number',
        'color',
        'wheel_size',
        'front_suspension',
        'rear_suspension',
        'brake_type',
        'purchase_price',
        'purchase_date',
        'notes',
        'photos',
        'retirement_reason',
        'retirement_comment',
        'retired_at',
        'unavailability_reason',
        'unavailability_comment',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(ModelEloquentModel::class, 'model_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryEloquentModel::class, 'category_id');
    }

    protected $casts = [
        'pricing_tier' => PricingTier::class,
        'year' => 'integer',
        'front_suspension' => 'integer',
        'rear_suspension' => 'integer',
        'frame_size_numeric' => 'float',
        'purchase_price' => 'float',
        'purchase_date' => 'date',
        'photos' => 'array',
        'retired_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
