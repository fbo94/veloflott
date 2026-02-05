<?php

declare(strict_types=1);

namespace Maintenance\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class MaintenanceEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'maintenances';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'bike_id',
        'type',
        'reason',
        'priority',
        'status',
        'description',
        'scheduled_at',
        'started_at',
        'completed_at',
        'work_description',
        'parts_replaced',
        'cost',
        'photos',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cost' => 'integer',
        'photos' => 'array',
    ];
}
