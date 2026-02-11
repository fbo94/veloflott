<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;
use Tenant\Domain\TenantRepositoryInterface;
use Tenant\Infrastructure\Keycloak\KeycloakAdminService;

final class SyncTenantsToKeycloak extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tenants:sync-keycloak
                            {--dry-run : Afficher ce qui serait fait sans l\'exécuter}
                            {--force : Forcer la synchronisation même si l\'organization existe}';

    /**
     * @var string
     */
    protected $description = 'Synchroniser les tenants PostgreSQL avec les organizations Keycloak';

    public function __construct(
        private readonly TenantRepositoryInterface $tenantRepository,
        private readonly KeycloakAdminService $keycloakAdmin,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🔄 Synchronisation des tenants vers Keycloak');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  Mode DRY-RUN activé - Aucune modification ne sera effectuée');
            $this->newLine();
        }

        // Récupérer tous les tenants
        $tenants = $this->tenantRepository->findAll();
        $tenantCount = count($tenants);
        $this->info("📊 {$tenantCount} tenant(s) trouvé(s) dans PostgreSQL");
        $this->newLine();

        // Récupérer les organizations existantes dans Keycloak
        $keycloakOrgs = [];
        $keycloakOrgsByName = [];

        try {
            $orgs = $this->keycloakAdmin->listOrganizations();
            foreach ($orgs as $org) {
                // Indexer par tenant_id (dans les attributs)
                $tenantId = $org['attributes']['tenant_id'][0] ?? null;
                if ($tenantId !== null) {
                    $keycloakOrgs[$tenantId] = $org;
                }
                // Indexer aussi par nom en backup
                $keycloakOrgsByName[strtolower($org['name'] ?? '')] = $org;
            }
            $orgCount = count($orgs);
            $this->info("📊 {$orgCount} organization(s) trouvée(s) dans Keycloak");
            $this->newLine();
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la récupération des organizations Keycloak: {$e->getMessage()}");

            return Command::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        // Pour chaque tenant
        foreach ($tenants as $tenant) {
            $slug = $tenant->slug();
            $name = $tenant->name();
            $tenantId = $tenant->id();

            $this->line("Processing: {$name} ({$slug})");

            // Vérifier si l'organization existe déjà (par tenant_id ou par nom)
            $existsInKeycloak = isset($keycloakOrgs[$tenantId]) || isset($keycloakOrgsByName[strtolower($name)]);

            if ($existsInKeycloak && !$isForce) {
                $this->line('  ⏭️  Organization existe déjà dans Keycloak');
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                if ($existsInKeycloak) {
                    $this->line("  🔄 Mettrait à jour l'organization dans Keycloak");
                } else {
                    $this->line('  ✨ Créerait une nouvelle organization dans Keycloak');
                }
                continue;
            }

            // Préparer les attributs
            $attributes = [
                'tenant_id' => [$tenant->id()],
                'subscription_plan_id' => [$tenant->subscriptionPlanId()],
                'max_users' => [(string) $tenant->maxUsers()],
                'max_bikes' => [(string) $tenant->maxBikes()],
                'max_sites' => [(string) $tenant->maxSites()],
            ];

            try {
                if ($existsInKeycloak) {
                    // Mettre à jour
                    $existingOrg = $keycloakOrgs[$tenantId] ?? $keycloakOrgsByName[strtolower($name)];
                    $orgId = $existingOrg['id'];
                    $success = $this->keycloakAdmin->updateOrganization(
                        organizationId: $orgId,
                        name: $name,
                        attributes: $attributes,
                        domainSlug: $slug
                    );

                    if ($success) {
                        $this->line('  ✅ Organization mise à jour');
                        $updated++;
                    } else {
                        $this->error('  ❌ Échec de la mise à jour');
                        $errors++;
                    }
                } else {
                    // Créer
                    $orgId = $this->keycloakAdmin->createOrganization(
                        name: $name,
                        alias: $slug,
                        attributes: $attributes
                    );

                    if ($orgId !== null) {
                        $this->line("  ✅ Organization créée (ID: {$orgId})");
                        $created++;
                    } else {
                        $this->error('  ❌ Échec de la création');
                        $errors++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur: {$e->getMessage()}");
                $this->logger->error('Sync error', [
                    'tenant_id' => $tenant->id(),
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->newLine();
        $this->info('✨ Synchronisation terminée');
        $this->newLine();

        // Afficher le résumé
        $this->table(
            ['Action', 'Nombre'],
            [
                ['Organizations créées', $created],
                ['Organizations mises à jour', $updated],
                ['Organizations ignorées', $skipped],
                ['Erreurs', $errors],
            ]
        );

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
