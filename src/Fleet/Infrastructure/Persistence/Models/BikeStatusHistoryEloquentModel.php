<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Tenant\Infrastructure\Persistence\Traits\SiteScoped;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class BikeStatusHistoryEloquentModel extends Model
{
    use TenantScoped;
    use SiteScoped;

    protected $table = 'bike_status_history';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
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
