<?php

declare(strict_types=1);

namespace Rental\Infrastructure\Persistence\Models;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Fleet\Infrastructure\Persistence\Models\CategoryEloquentModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pricing\Infrastructure\Persistence\Models\PricingClassEloquentModel;
use Tenant\Infrastructure\Persistence\Traits\TenantScoped;

final class DepositRetentionConfigEloquentModel extends Model
{
    use HasUuids;
    use TenantScoped;

    protected $table = 'deposit_retention_configs';

    protected $fillable = [
        'id',
        'tenant_id',
        'bike_id',
        'pricing_class_id',
        'category_id',
        'minor_damage_amount',
        'major_damage_amount',
        'total_loss_amount',
    ];

    protected $casts = [
        'minor_damage_amount' => 'float',
        'major_damage_amount' => 'float',
        'total_loss_amount' => 'float',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<BikeEloquentModel, $this>
     */
    public function bike(): BelongsTo
    {
        return $this->belongsTo(BikeEloquentModel::class, 'bike_id');
    }

    /**
     * @return BelongsTo<PricingClassEloquentModel, $this>
     */
    public function pricingClass(): BelongsTo
    {
        return $this->belongsTo(PricingClassEloquentModel::class, 'pricing_class_id');
    }

    /**
     * @return BelongsTo<CategoryEloquentModel, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryEloquentModel::class, 'category_id');
    }
}
