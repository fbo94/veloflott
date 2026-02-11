<?php

declare(strict_types=1);

namespace Auth\Application\GetCurrentUser;

use Auth\Domain\User;
use Tenant\Domain\Tenant;

final readonly class GetCurrentUserResponse
{
    /**
     * @param array<int, string> $permissions
     * @param array<string, mixed>|null $tenant
     */
    public function __construct(
        public string $id,
        public string $email,
        public ?string $firstName,
        public ?string $lastName,
        public string $fullName,
        public string $role,
        public string $roleLabel,
        public bool $isActive,
        public array $permissions,
        public ?string $lastLoginAt,
        public ?array $tenant = null,
    ) {
    }

    public static function fromUser(User $user, ?Tenant $tenant = null): self
    {
        return new self(
            id: $user->id(),
            email: $user->email(),
            firstName: $user->firstName(),
            lastName: $user->lastName(),
            fullName: $user->fullName(),
            role: $user->role()->value,
            roleLabel: $user->role()->label(),
            isActive: $user->isActive(),
            permissions: array_map(
                fn ($permission) => $permission->value,
                $user->permissions()
            ),
            lastLoginAt: $user->lastLoginAt()?->format('c'),
            tenant: $tenant !== null ? self::tenantToArray($tenant) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function tenantToArray(Tenant $tenant): array
    {
        return [
            'id' => $tenant->id(),
            'name' => $tenant->name(),
            'slug' => $tenant->slug(),
            'domain' => $tenant->domain(),
            'status' => $tenant->status()->value,
            'contact_email' => $tenant->contactEmail(),
            'contact_phone' => $tenant->contactPhone(),
            'address' => $tenant->address(),
            'logo_url' => $tenant->logoUrl(),
            'subscription_plan_id' => $tenant->subscriptionPlanId(),
            'max_users' => $tenant->maxUsers(),
            'max_bikes' => $tenant->maxBikes(),
            'max_sites' => $tenant->maxSites(),
            'is_in_trial' => $tenant->isInTrial(),
            'trial_ends_at' => $tenant->trialEndsAt()?->format('c'),
            'onboarding_completed' => $tenant->onboardingCompleted(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'full_name' => $this->fullName,
            'role' => $this->role,
            'role_label' => $this->roleLabel,
            'is_active' => $this->isActive,
            'permissions' => $this->permissions,
            'last_login_at' => $this->lastLoginAt,
            'tenant' => $this->tenant,
        ];
    }
}
