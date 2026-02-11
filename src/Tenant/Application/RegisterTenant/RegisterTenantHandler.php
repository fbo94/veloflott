<?php

declare(strict_types=1);

namespace Tenant\Application\RegisterTenant;

use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Domain\TenantStatus;

final readonly class RegisterTenantHandler
{
    private const TRIAL_DAYS = 30;

    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
    ) {
    }

    public function handle(RegisterTenantCommand $command): RegisterTenantResponse
    {
        // Récupérer le plan d'abonnement
        $subscriptionPlan = $this->subscriptionPlanRepository->findById($command->subscriptionPlanId);
        if ($subscriptionPlan === null) {
            throw new \DomainException("Subscription plan not found: {$command->subscriptionPlanId}");
        }

        if (!$subscriptionPlan->isActive()) {
            throw new \DomainException("Subscription plan '{$subscriptionPlan->name()}' is not active.");
        }

        // Générer un slug unique depuis le nom de l'organisation
        $baseSlug = Str::slug($command->organizationName);
        $slug = $this->generateUniqueSlug($baseSlug);

        // Calculer la date de fin d'essai (30 jours)
        $trialEndsAt = new \DateTimeImmutable('+' . self::TRIAL_DAYS . ' days');

        // Créer le tenant avec période d'essai
        $tenant = new Tenant(
            id: Uuid::uuid4()->toString(),
            name: $command->organizationName,
            slug: $slug,
            domain: null,
            status: TenantStatus::ACTIVE,
            contactEmail: $command->ownerEmail,
            contactPhone: null,
            settings: null,
            address: null,
            logoUrl: null,
            subscriptionPlanId: $subscriptionPlan->id(),
            maxUsers: $subscriptionPlan->maxUsers(),
            maxBikes: $subscriptionPlan->maxBikes(),
            maxSites: $subscriptionPlan->maxSites(),
            trialEndsAt: $trialEndsAt,
            onboardingCompleted: false,
        );

        $this->tenantRepository->save($tenant);

        return new RegisterTenantResponse(
            tenantId: $tenant->id(),
            tenantName: $tenant->name(),
            tenantSlug: $tenant->slug(),
            ownerEmail: $command->ownerEmail,
            ownerName: $command->ownerName,
            subscriptionPlanName: $subscriptionPlan->displayName(),
            trialEndsAt: $trialEndsAt->format('Y-m-d H:i:s'),
            message: "Organisation créée avec succès. Période d'essai de " . self::TRIAL_DAYS . ' jours activée.',
        );
    }

    private function generateUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while ($this->tenantRepository->existsWithSlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
