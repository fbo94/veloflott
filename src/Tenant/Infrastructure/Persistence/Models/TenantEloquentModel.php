<?php

declare(strict_types=1);

namespace Tenant\Infrastructure\Persistence\Models;

use Fleet\Infrastructure\Persistence\Models\BikeEloquentModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Subscription\Infrastructure\Persistence\Models\SubscriptionPlanEloquentModel;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string $status
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property array<string, mixed>|null $settings
 * @property string|null $address
 * @property string|null $logo_url
 * @property string|null $subscription_plan_id
 * @property int $max_users
 * @property int $max_bikes
 * @property int $max_sites
 * @property \DateTimeImmutable|null $trial_ends_at
 * @property bool $onboarding_completed
 * @property \DateTimeImmutable $created_at
 * @property \DateTimeImmutable $updated_at
 */
final class TenantEloquentModel extends Model
{
    use HasUuids;

    protected $table = 'tenants';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'domain',
        'status',
        'contact_email',
        'contact_phone',
        'settings',
        'address',
        'logo_url',
        'subscription_plan_id',
        'max_users',
        'max_bikes',
        'max_sites',
        'trial_ends_at',
        'onboarding_completed',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'immutable_datetime',
        'onboarding_completed' => 'boolean',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<SubscriptionPlanEloquentModel, $this>
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlanEloquentModel::class, 'subscription_plan_id');
    }

    /**
     * @return HasMany<BikeEloquentModel, $this>
     */
    public function bikes(): HasMany
    {
        return $this->hasMany(BikeEloquentModel::class, 'tenant_id');
    }

    /**
     * @return HasMany<SiteEloquentModel, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(SiteEloquentModel::class, 'tenant_id');
    }

    /**
     * @return HasMany<SiteEloquentModel, $this>
     */
    public function activeSites(): HasMany
    {
        return $this->hasMany(SiteEloquentModel::class, 'tenant_id')
            ->where('status', 'active');
    }
}
