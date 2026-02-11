<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Tenant\Infrastructure\Persistence\Models\TenantEloquentModel;

class ReassignDataToTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:reassign-data {tenant-slug : Le slug du tenant (ex: veloflott-paris)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réaffecter toutes les données de l\'application à un tenant spécifique';

    /**
     * Tables avec tenant_id à mettre à jour
     *
     * @var array<int, string>
     */
    private array $tenantTables = [
        'bikes',
        'bike_status_histories',
        'categories',
        'customers',
        'deposit_retention_configs',
        'discount_rules',
        'duration_definitions',
        'maintenances',
        'pricing_classes',
        'pricing_rates',
        'rates',
        'rentals',
        'rental_equipment',
        'rental_items',
        'rental_settings',
        'sites',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantSlug = $this->argument('tenant-slug');

        // Trouver le tenant
        $tenant = TenantEloquentModel::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            $this->error("Tenant '{$tenantSlug}' non trouvé !");

            $createNew = $this->confirm("Voulez-vous créer un nouveau tenant '{$tenantSlug}' ?");

            if ($createNew) {
                $tenant = $this->createTenant($tenantSlug);
            } else {
                return 1;
            }
        }

        $this->info("Tenant trouvé : {$tenant->name} (ID: {$tenant->id})");

        if (!$this->confirm("Êtes-vous sûr de vouloir réaffecter TOUTES les données à ce tenant ?")) {
            $this->info('Opération annulée.');
            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($this->tenantTables as $table) {
                $this->reassignTable($table, $tenant->id);
            }

            DB::commit();

            $this->info('✓ Toutes les données ont été réaffectées avec succès !');

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur lors de la réaffectation : {$e->getMessage()}");
            return 1;
        }
    }

    private function createTenant(string $slug): TenantEloquentModel
    {
        $name = $this->ask('Nom du tenant', 'VéloFlott Paris');
        $address = $this->ask('Adresse', '123 Rue de Rivoli, 75001 Paris');
        $email = $this->ask('Email de contact', 'contact@veloflott.fr');
        $phone = $this->ask('Téléphone', '+33123456789');

        $tenant = new TenantEloquentModel();
        $tenant->id = \Illuminate\Support\Str::uuid()->toString();
        $tenant->name = $name;
        $tenant->slug = $slug;
        $tenant->address = $address;
        $tenant->contact_email = $email;
        $tenant->contact_phone = $phone;
        $tenant->subscription_plan_id = DB::table('subscription_plans')->where('name', 'free')->value('id');
        $tenant->max_users = 5;
        $tenant->max_bikes = 50;
        $tenant->max_sites = 1;
        $tenant->status = 'active';
        $tenant->save();

        $this->info("✓ Tenant '{$name}' créé avec succès !");

        return $tenant;
    }

    private function reassignTable(string $table, string $tenantId): void
    {
        // Vérifier si la table existe
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            $this->warn("  ⊘ Table '{$table}' n'existe pas, ignorée.");
            return;
        }

        // Vérifier si la table a une colonne tenant_id
        if (!DB::getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
            $this->warn("  ⊘ Table '{$table}' n'a pas de colonne tenant_id, ignorée.");
            return;
        }

        $count = DB::table($table)->whereNull('tenant_id')->count();

        if ($count === 0) {
            $this->line("  - Table '{$table}' : Aucune donnée à réaffecter");
            return;
        }

        DB::table($table)
            ->whereNull('tenant_id')
            ->update(['tenant_id' => $tenantId]);

        $this->info("  ✓ Table '{$table}' : {$count} enregistrement(s) réaffecté(s)");
    }
}
