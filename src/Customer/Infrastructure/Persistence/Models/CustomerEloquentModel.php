<?php

declare(strict_types=1);

namespace Customer\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tenant\Infrastructure\Persistence\Models\SiteEloquentModel;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;
use Tenant\Infrastructure\Persistence\Traits\SiteScoped;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class CustomerEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;
    use SiteScoped;

    protected $table = 'customers';

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'identity_document_type',
        'identity_document_number',
        'height',
        'weight',
        'address',
        'notes',
        'photos',
        'is_risky',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_risky' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<TenantEloquentModel, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantEloquentModel::class, 'tenant_id');
    }

    /**
     * @return BelongsTo<SiteEloquentModel, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(SiteEloquentModel::class, 'site_id');
    }
}
