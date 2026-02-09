<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class BrandEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;

    protected $table = 'brands';

    protected $fillable = [
        'tenant_id',
        'name',
        'logo_url',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(ModelEloquentModel::class, 'brand_id');
    }
}
