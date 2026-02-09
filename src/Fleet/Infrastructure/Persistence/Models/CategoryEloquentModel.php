<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class CategoryEloquentModel extends Model
{
    use TenantScoped;

    protected $table = 'categories';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_default',
        'display_order',
        'parent_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
