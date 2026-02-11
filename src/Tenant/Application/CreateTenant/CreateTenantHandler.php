<?php

declare(strict_types=1);

namespace Tenant\Application\CreateTenant;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Domain\Tenant;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Domain\TenantStatus;
use Tenant\Infrastructure\Keycloak\KeycloakAdminService;

final readonly class CreateTenantHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        private KeycloakAdminService $keycloakAdmin,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(CreateTenantCommand $command): CreateTenantResponse
    {
        // Vérifier que le slug est unique
        $existingTenant = $this->tenantRepository->findBySlug($command->slug);
        if ($existingTenant !== null) {
            throw new \DomainException("A tenant with slug '{$command->slug}' already exists.");
        }

        // Récupérer le plan d'abonnement
        $subscriptionPlan = $this->subscriptionPlanRepository->findById($command->subscriptionPlanId);
        if ($subscriptionPlan === null) {
            throw new \DomainException("Subscription plan not found: {$command->subscriptionPlanId}");
        }

        if (!$subscriptionPlan->isActive()) {
            throw new \DomainException("Subscription plan '{$subscriptionPlan->name()}' is not active.");
        }

        // Créer le tenant avec les limites du plan (snapshot)
        $tenant = new Tenant(
            id: Uuid::uuid4()->toString(),
            name: $command->name,
            slug: $command->slug,
            domain: null, // À définir plus tard si besoin
            status: TenantStatus::ACTIVE,
            contactEmail: $command->contactEmail,
            contactPhone: $command->contactPhone,
            settings: null,
            address: $command->address,
            logoUrl: $command->logoUrl,
            subscriptionPlanId: $subscriptionPlan->id(),
            maxUsers: $subscriptionPlan->maxUsers(),
            maxBikes: $subscriptionPlan->maxBikes(),
            maxSites: $subscriptionPlan->maxSites(),
        );

        $this->tenantRepository->save($tenant);

        // Créer l'organization dans Keycloak
        try {
            $keycloakOrgId = $this->keycloakAdmin->createOrganization(
                name: $tenant->name(),
                alias: $tenant->slug(),
                attributes: [
                    'tenant_id' => [$tenant->id()],
                    'subscription_plan_id' => [$tenant->subscriptionPlanId()],
                    'max_users' => [(string) $tenant->maxUsers()],
                    'max_bikes' => [(string) $tenant->maxBikes()],
                    'max_sites' => [(string) $tenant->maxSites()],
                ]
            );

            if ($keycloakOrgId !== null) {
                $this->logger->info('Keycloak organization created', [
                    'tenant_id' => $tenant->id(),
                    'keycloak_org_id' => $keycloakOrgId,
                    'slug' => $tenant->slug(),
                ]);
            } else {
                $this->logger->warning('Failed to create Keycloak organization', [
                    'tenant_id' => $tenant->id(),
                    'slug' => $tenant->slug(),
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error creating Keycloak organization', [
                'tenant_id' => $tenant->id(),
                'error' => $e->getMessage(),
            ]);
            // Ne pas bloquer la création du tenant si Keycloak échoue
        }

        return new CreateTenantResponse(
            id: $tenant->id(),
            name: $tenant->name(),
            slug: $tenant->slug(),
            address: $tenant->address(),
            contactEmail: $tenant->contactEmail(),
            contactPhone: $tenant->contactPhone(),
            logoUrl: $tenant->logoUrl(),
            subscriptionPlanId: $tenant->subscriptionPlanId(),
            subscriptionPlanName: $subscriptionPlan->name(),
            subscriptionPlanDisplayName: $subscriptionPlan->displayName(),
            maxUsers: $tenant->maxUsers(),
            maxBikes: $tenant->maxBikes(),
            maxSites: $tenant->maxSites(),
            status: $tenant->status()->value,
            createdAt: $tenant->createdAt()->format('Y-m-d H:i:s'),
        );
    }
}
