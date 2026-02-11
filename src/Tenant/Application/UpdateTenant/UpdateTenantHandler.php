<?php

declare(strict_types=1);

namespace Tenant\Application\UpdateTenant;

use Psr\Log\LoggerInterface;
use Subscription\Domain\SubscriptionPlanRepositoryInterface;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Infrastructure\Keycloak\KeycloakAdminService;

final readonly class UpdateTenantHandler
{
    public function __construct(
        private TenantRepositoryInterface $tenantRepository,
        private SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        private KeycloakAdminService $keycloakAdmin,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(UpdateTenantCommand $command): UpdateTenantResponse
    {
        // Récupérer le tenant existant
        $tenant = $this->tenantRepository->findById($command->id);
        if ($tenant === null) {
            throw new \DomainException("Tenant not found: {$command->id}");
        }

        // Vérifier que le slug est unique (sauf pour le tenant actuel)
        if ($command->slug !== $tenant->slug()) {
            $existingTenant = $this->tenantRepository->findBySlug($command->slug);
            if ($existingTenant !== null) {
                throw new \DomainException("A tenant with slug '{$command->slug}' already exists.");
            }
        }

        // Vérifier le plan d'abonnement
        $subscriptionPlan = $this->subscriptionPlanRepository->findById($command->subscriptionPlanId);
        if ($subscriptionPlan === null) {
            throw new \DomainException("Subscription plan not found: {$command->subscriptionPlanId}");
        }

        if (!$subscriptionPlan->isActive()) {
            throw new \DomainException("Subscription plan '{$subscriptionPlan->name()}' is not active.");
        }

        // Mettre à jour le tenant
        $tenant->updateInformation(
            name: $command->name,
            slug: $command->slug,
            contactEmail: $command->contactEmail,
            contactPhone: $command->contactPhone,
            address: $command->address,
            logoUrl: $command->logoUrl,
        );

        // Mettre à jour le plan d'abonnement et les limites
        $tenant->updateSubscriptionPlan(
            subscriptionPlanId: $subscriptionPlan->id(),
            maxUsers: $command->maxUsers,
            maxBikes: $command->maxBikes,
            maxSites: $command->maxSites,
        );

        // Sauvegarder
        $this->tenantRepository->save($tenant);

        // Synchroniser avec Keycloak
        $this->syncWithKeycloak($tenant);

        return new UpdateTenantResponse(
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
            updatedAt: $tenant->updatedAt()->format('Y-m-d H:i:s'),
        );
    }

    private function syncWithKeycloak(\Tenant\Domain\Tenant $tenant): void
    {
        try {
            // Trouver l'organization Keycloak correspondante
            $orgs = $this->keycloakAdmin->listOrganizations();
            $keycloakOrgId = null;

            foreach ($orgs as $org) {
                $tenantId = $org['attributes']['tenant_id'][0] ?? null;
                if ($tenantId === $tenant->id()) {
                    $keycloakOrgId = $org['id'];
                    break;
                }
            }

            if ($keycloakOrgId !== null) {
                // Mettre à jour l'organization existante
                $this->keycloakAdmin->updateOrganization(
                    organizationId: $keycloakOrgId,
                    name: $tenant->name(),
                    attributes: [
                        'tenant_id' => [$tenant->id()],
                        'subscription_plan_id' => [$tenant->subscriptionPlanId()],
                        'max_users' => [(string) $tenant->maxUsers()],
                        'max_bikes' => [(string) $tenant->maxBikes()],
                        'max_sites' => [(string) $tenant->maxSites()],
                        'slug' => [$tenant->slug()],
                    ],
                );

                $this->logger->info('Keycloak organization updated', [
                    'tenant_id' => $tenant->id(),
                    'keycloak_org_id' => $keycloakOrgId,
                ]);
            } else {
                // Créer l'organization si elle n'existe pas
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

                $this->logger->info('Keycloak organization created during update', [
                    'tenant_id' => $tenant->id(),
                    'keycloak_org_id' => $keycloakOrgId,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error syncing tenant with Keycloak', [
                'tenant_id' => $tenant->id(),
                'error' => $e->getMessage(),
            ]);
            // Ne pas bloquer la mise à jour du tenant si Keycloak échoue
        }
    }
}
