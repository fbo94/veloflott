<?php

declare(strict_types=1);

namespace Tenant\Application\RegisterTenant;

final readonly class RegisterTenantResponse
{
    public function __construct(
        public string $tenantId,
        public string $tenantName,
        public string $tenantSlug,
        public string $ownerEmail,
        public string $ownerName,
        public string $subscriptionPlanName,
        public string $trialEndsAt,
        public string $message,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant' => [
                'id' => $this->tenantId,
                'name' => $this->tenantName,
                'slug' => $this->tenantSlug,
            ],
            'owner' => [
                'name' => $this->ownerName,
                'email' => $this->ownerEmail,
            ],
            'subscription' => [
                'plan_name' => $this->subscriptionPlanName,
                'trial_ends_at' => $this->trialEndsAt,
            ],
            'message' => $this->message,
            'next_steps' => [
                'Créez votre compte utilisateur via Keycloak',
                'Validez votre adresse email',
                'Connectez-vous à votre organisation',
                'Complétez votre onboarding',
            ],
        ];
    }
}
