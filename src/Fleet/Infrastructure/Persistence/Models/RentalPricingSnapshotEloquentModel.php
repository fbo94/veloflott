<?php

declare(strict_types=1);

namespace Fleet\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Tenant\Infrastructure\Persistence\Traits\SiteScoped;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class RentalPricingSnapshotEloquentModel extends Model
{
    use TenantScoped;
    use SiteScoped;

    protected $table = 'rental_pricing_snapshots';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'site_id',
        'rental_id',
        'base_price',
        'final_price',
        'discounts_applied',
        'category_id',
        'pricing_class_id',
        'duration_id',
        'days',
        'price_per_day',
        'calculated_at',
    ];

    protected $casts = [
        'base_price' => 'float',
        'final_price' => 'float',
        'discounts_applied' => 'array',
        'days' => 'integer',
        'price_per_day' => 'float',
        'calculated_at' => 'immutable_datetime',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
