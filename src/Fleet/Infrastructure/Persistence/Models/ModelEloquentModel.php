<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class ModelEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;

    protected $table = 'models';

    protected $fillable = [
        'tenant_id',
        'name',
        'brand_id',
    ];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandEloquentModel::class, 'brand_id');
    }

    public function bikes(): HasMany
    {
        return $this->hasMany(BikeEloquentModel::class, 'model_id');
    }
}
