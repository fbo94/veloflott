<?php

declare(strict_types=1);

namespace Subscription\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string $display_name
 * @property string|null $description
 * @property float|null $price_monthly
 * @property float|null $price_yearly
 * @property int $max_users
 * @property int $max_bikes
 * @property int $max_sites
 * @property array<string, mixed>|null $features
 * @property bool $is_active
 * @property int $sort_order
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 */
final class SubscriptionPlanEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'subscription_plans';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'display_name',
        'description',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_bikes',
        'max_sites',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_users' => 'integer',
        'max_bikes' => 'integer',
        'max_sites' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];
}
