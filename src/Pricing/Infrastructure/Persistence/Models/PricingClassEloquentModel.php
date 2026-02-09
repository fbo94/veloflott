<?php

declare(strict_types=1);

namespace Pricing\Infrastructure\Persistence\Models;

use Database\Factories\PricingClassFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

/**
 * @phpstan-use HasFactory<PricingClassFactory>
 */
final class PricingClassEloquentModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    use TenantScoped;

    protected $table = 'pricing_classes';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'code',
        'label',
        'description',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'deleted_at' => 'immutable_datetime',
    ];

    protected static function newFactory(): PricingClassFactory
    {
        return PricingClassFactory::new();
    }
}
