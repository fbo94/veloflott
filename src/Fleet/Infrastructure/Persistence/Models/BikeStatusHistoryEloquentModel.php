<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class BikeStatusHistoryEloquentModel extends Model
{
    protected $table = 'bike_status_history';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'bike_id',
        'old_status',
        'new_status',
        'unavailability_reason',
        'unavailability_comment',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'immutable_datetime',
    ];

    public $timestamps = false;
}
