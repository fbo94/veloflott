# 📋 Spécification Complète - Système de Tarification 3D

**Destinataire :** Équipe Backend (Laravel DDD)
**Date :** 2026-02-06
**Version :** MVP Simplifié (Niveau Tenant uniquement)

---

## 🎯 Vue d'ensemble

### Objectif

Créer un système de tarification flexible et configurable permettant aux loueurs de définir leurs tarifs selon **3 dimensions** :

1. **Catégorie de vélo** (VTT, Route, Ville, Électrique, etc.)
2. **Classe de tarification** (Standard, Premium, Luxe, + classes personnalisées)
3. **Durée de location** (Demi-journée, Journée, Week-end, Semaine, + durées personnalisées)

### Périmètre MVP

✅ **Inclus :**
- Grille 3D au niveau Tenant uniquement
- Classes et durées configurables par tenant
- Réductions dégressives automatiques
- Calcul automatique des prix
- Validation stricte avec options de déblocage
- Historisation des tarifs

❌ **Exclu (phases futures) :**
- Cascade App/Site/Vélo (Phase 2)
- Copie en masse (Phase 2)
- Import/Export CSV (Phase 3)

---

## 🏗️ Architecture DDD

### Bounded Context

**Pricing** (nouveau contexte) ou intégré dans **Fleet** (existant)

### Aggregates

```
PricingConfiguration (Aggregate Root)
├── PricingClasses (Entities)
├── DurationDefinitions (Entities)
├── PricingRates (Value Objects)
└── DiscountRules (Value Objects)
```

---

## 📊 Modèle de données

### 1. `pricing_classes` (Classes de tarification)

```php
// Migration
Schema::create('pricing_classes', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('tenant_id');

    $table->string('code')->index();           // 'standard', 'premium', 'luxe', 'vip'
    $table->string('label');                   // "Standard", "Premium", "Luxe"
    $table->text('description')->nullable();
    $table->string('color', 7)->nullable();    // #3B82F6 (pour UI)

    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->unique(['tenant_id', 'code']);
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

**Règles métier :**
- Le `code` doit être unique par tenant (slug format: lowercase, alphanumeric, underscore)
- Au moins une classe doit être active par tenant
- Suppression interdite si des vélos utilisent cette classe

---

### 2. `duration_definitions` (Durées de location)

```php
// Migration
Schema::create('duration_definitions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('tenant_id');

    $table->string('code')->index();           // 'half_day', 'full_day', 'weekend'
    $table->string('label');                   // "Demi-journée", "Journée"

    $table->integer('duration_hours')->nullable();  // 4 pour demi-journée
    $table->integer('duration_days')->nullable();   // 1 pour journée, 7 pour semaine
    $table->boolean('is_custom')->default(false);   // true = "Durée personnalisée"

    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->unique(['tenant_id', 'code']);
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

**Règles métier :**
- Le `code` doit être unique par tenant
- `duration_hours` OU `duration_days` doit être renseigné (pas les deux sauf cas spéciaux)
- `is_custom = true` → utilisée pour les durées personnalisées (pas de tarif fixe)
- Au moins une durée doit être active par tenant

---

### 3. `pricing_rates` (Grille 3D - Tarifs)

```php
// Migration
Schema::create('pricing_rates', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('tenant_id');

    // Les 3 dimensions
    $table->uuid('category_id');           // FK vers categories
    $table->uuid('pricing_class_id');      // FK vers pricing_classes
    $table->uuid('duration_id');           // FK vers duration_definitions

    // Le prix
    $table->decimal('price', 10, 2);       // En euros (ex: 35.00)

    // Métadonnées
    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    // Indexes & Contraintes
    $table->unique(['tenant_id', 'category_id', 'pricing_class_id', 'duration_id'],
                   'unique_pricing_rate');

    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
    $table->foreign('pricing_class_id')->references('id')->on('pricing_classes')->onDelete('restrict');
    $table->foreign('duration_id')->references('id')->on('duration_definitions')->onDelete('restrict');
});
```

**Règles métier :**
- Une seule combinaison `(category × class × duration)` par tenant
- Prix > 0
- **Combinaisons vides autorisées** : toutes les combinaisons n'ont pas besoin d'avoir un tarif
- Suppression interdite si utilisée dans une location active

---

### 4. `discount_rules` (Réductions dégressives)

```php
// Migration
Schema::create('discount_rules', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('tenant_id');

    // Applicabilité (nullable = toutes)
    $table->uuid('category_id')->nullable();      // null = toutes catégories
    $table->uuid('pricing_class_id')->nullable(); // null = toutes classes

    // Condition de déclenchement
    $table->integer('min_days')->nullable();              // Ex: 3 jours
    $table->uuid('min_duration_id')->nullable();          // Ex: "À partir de week-end"

    // Réduction
    $table->enum('discount_type', ['percentage', 'fixed']);  // percentage ou montant fixe
    $table->decimal('discount_value', 10, 2);                // 10 pour 10% ou 10€

    // UI
    $table->string('label');                      // "Réduction longue durée -10%"
    $table->text('description')->nullable();

    // Cumul
    $table->boolean('is_cumulative')->default(false);
    $table->integer('priority')->default(0);      // Ordre d'application

    $table->boolean('is_active')->default(true);

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
    $table->foreign('pricing_class_id')->references('id')->on('pricing_classes')->onDelete('cascade');
    $table->foreign('min_duration_id')->references('id')->on('duration_definitions')->onDelete('cascade');
});
```

**Règles métier :**
- `min_days` OU `min_duration_id` doit être renseigné
- `discount_value > 0`
- Si `discount_type = percentage`, `discount_value <= 100`
- Les réductions s'appliquent sur le prix total (après multiplication par nb jours)

---

### 5. `rental_pricing_snapshots` (Historique - Immuable)

```php
// Migration
Schema::create('rental_pricing_snapshots', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('rental_id')->unique();
    $table->uuid('tenant_id');

    // Prix calculé (immuable)
    $table->decimal('base_price', 10, 2);         // Prix de base (tarif × jours)
    $table->decimal('final_price', 10, 2);        // Prix final après réductions
    $table->json('discounts_applied')->nullable(); // Détail des réductions

    // Traçabilité des sources
    $table->uuid('category_id');
    $table->uuid('pricing_class_id');
    $table->uuid('duration_id');
    $table->integer('days');
    $table->decimal('price_per_day', 10, 2);

    // Métadonnées
    $table->timestamp('calculated_at');

    $table->timestamps();

    // Indexes
    $table->foreign('rental_id')->references('id')->on('rentals')->onDelete('cascade');
    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
});
```

**Règles métier :**
- **Immuable** : une fois créé, ne peut plus être modifié
- Créé automatiquement à la création de la location
- Permet de garder l'historique exact du calcul de prix

---

### 6. Ajout champ `pricing_class_id` sur `bikes`

```php
// Migration (alter table)
Schema::table('bikes', function (Blueprint $table) {
    $table->uuid('pricing_class_id')->nullable()->after('category_id');

    $table->foreign('pricing_class_id')
          ->references('id')
          ->on('pricing_classes')
          ->onDelete('restrict');

    $table->index('pricing_class_id');
});
```

**Règles métier :**
- `pricing_class_id` obligatoire pour qu'un vélo soit louable
- Vélo sans classe = non louable

---

## 🎲 Logique métier (Domain Services)

### Service : `PricingCalculator`

```php
namespace Domain\Pricing\Services;

use Domain\Pricing\ValueObjects\PriceCalculation;
use Domain\Pricing\Exceptions\NoPricingFoundException;

class PricingCalculator
{
    /**
     * Calcule le prix d'une location
     *
     * @throws NoPricingFoundException
     */
    public function calculate(
        string $tenantId,
        string $categoryId,
        string $pricingClassId,
        string $durationId,
        ?int $customDays = null
    ): PriceCalculation {
        // 1. Récupérer la durée
        $duration = $this->durationRepository->findByIdAndTenant($durationId, $tenantId);

        // 2. Calculer le nombre de jours
        $days = $customDays ?? $duration->duration_days ?? 1;

        // 3. Résoudre le tarif de base
        $rate = $this->findRate($tenantId, $categoryId, $pricingClassId, $durationId);

        if (!$rate) {
            throw new NoPricingFoundException(
                "Aucun tarif trouvé pour cette combinaison"
            );
        }

        // 4. Prix de base = tarif × jours
        $basePrice = $rate->price * $days;

        // 5. Appliquer les réductions dégressives
        $discounts = $this->getApplicableDiscounts(
            $tenantId,
            $categoryId,
            $pricingClassId,
            $days
        );

        $finalPrice = $basePrice;
        $appliedDiscounts = [];

        foreach ($discounts as $discount) {
            $discountAmount = $discount->discount_type === 'percentage'
                ? ($finalPrice * $discount->discount_value / 100)
                : $discount->discount_value;

            $finalPrice -= $discountAmount;

            $appliedDiscounts[] = [
                'label' => $discount->label,
                'type' => $discount->discount_type,
                'value' => $discount->discount_value,
                'amount' => $discountAmount,
            ];
        }

        // 6. Retourner le calcul détaillé
        return new PriceCalculation(
            basePrice: $basePrice,
            finalPrice: max($finalPrice, 0), // Prix ne peut pas être négatif
            days: $days,
            pricePerDay: $rate->price,
            discounts: $appliedDiscounts,
            categoryId: $categoryId,
            pricingClassId: $pricingClassId,
            durationId: $durationId
        );
    }

    /**
     * Trouve le tarif pour une combinaison donnée
     */
    private function findRate(
        string $tenantId,
        string $categoryId,
        string $pricingClassId,
        string $durationId
    ): ?PricingRate {
        return PricingRate::where('tenant_id', $tenantId)
            ->where('category_id', $categoryId)
            ->where('pricing_class_id', $pricingClassId)
            ->where('duration_id', $durationId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Récupère les réductions applicables
     */
    private function getApplicableDiscounts(
        string $tenantId,
        string $categoryId,
        string $pricingClassId,
        int $days
    ): Collection {
        return DiscountRule::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->whereNull('category_id')
                      ->orWhere('category_id', $categoryId);
            })
            ->where(function ($query) use ($pricingClassId) {
                $query->whereNull('pricing_class_id')
                      ->orWhere('pricing_class_id', $pricingClassId);
            })
            ->where(function ($query) use ($days) {
                $query->where('min_days', '<=', $days)
                      ->orWhereNull('min_days');
            })
            ->orderBy('priority', 'asc')
            ->get();
    }
}
```

---

### Service : `PricingValidator`

```php
namespace Domain\Pricing\Services;

class PricingValidator
{
    /**
     * Vérifie si un vélo peut être loué (a un tarif)
     */
    public function canBikeBeRented(Bike $bike): bool
    {
        if (!$bike->pricing_class_id) {
            return false;
        }

        // Vérifie qu'au moins une durée active a un tarif
        $hasAnyRate = PricingRate::where('tenant_id', $bike->tenant_id)
            ->where('category_id', $bike->category_id)
            ->where('pricing_class_id', $bike->pricing_class_id)
            ->where('is_active', true)
            ->exists();

        return $hasAnyRate;
    }

    /**
     * Retourne les durées disponibles pour un vélo
     */
    public function getAvailableDurations(Bike $bike): Collection
    {
        if (!$bike->pricing_class_id) {
            return collect();
        }

        return DurationDefinition::where('tenant_id', $bike->tenant_id)
            ->where('is_active', true)
            ->whereHas('pricingRates', function ($query) use ($bike) {
                $query->where('category_id', $bike->category_id)
                      ->where('pricing_class_id', $bike->pricing_class_id)
                      ->where('is_active', true);
            })
            ->orderBy('sort_order')
            ->get();
    }
}
```

---

## 🔌 Endpoints API

### Classes de tarification

```php
// Routes
Route::prefix('pricing')->group(function () {
    Route::get('/classes', [PricingClassController::class, 'index']);
    Route::post('/classes', [PricingClassController::class, 'store']);
    Route::put('/classes/{id}', [PricingClassController::class, 'update']);
    Route::delete('/classes/{id}', [PricingClassController::class, 'destroy']);
});
```

#### GET `/api/fleet/pricing/classes`

```json
// Response 200
{
  "data": [
    {
      "id": "uuid",
      "code": "standard",
      "label": "Standard",
      "description": null,
      "color": "#3B82F6",
      "sort_order": 1,
      "is_active": true
    },
    {
      "id": "uuid",
      "code": "premium",
      "label": "Premium",
      "color": "#8B5CF6",
      "sort_order": 2,
      "is_active": true
    }
  ]
}
```

#### POST `/api/fleet/pricing/classes`

```json
// Request
{
  "code": "vip",
  "label": "VIP",
  "description": "Vélos haut de gamme",
  "color": "#F59E0B",
  "sort_order": 3
}

// Response 201
{
  "id": "uuid",
  "code": "vip",
  "label": "VIP",
  // ...
}
```

#### PUT `/api/fleet/pricing/classes/{id}`

```json
// Request
{
  "label": "VIP Premium",
  "color": "#EF4444"
}
```

#### DELETE `/api/fleet/pricing/classes/{id}`

```json
// Response 400 (si utilisé)
{
  "message": "Impossible de supprimer une classe utilisée par des vélos",
  "bikes_count": 15
}

// Response 204 (si OK)
```

---

### Durées

#### GET `/api/fleet/pricing/durations`

```json
{
  "data": [
    {
      "id": "uuid",
      "code": "half_day",
      "label": "Demi-journée",
      "duration_hours": 4,
      "duration_days": null,
      "is_custom": false,
      "sort_order": 1,
      "is_active": true
    },
    {
      "id": "uuid",
      "code": "full_day",
      "label": "Journée",
      "duration_hours": null,
      "duration_days": 1,
      "sort_order": 2,
      "is_active": true
    }
  ]
}
```

#### POST `/api/fleet/pricing/durations`

```json
// Request
{
  "code": "three_days",
  "label": "3 jours",
  "duration_days": 3,
  "sort_order": 4
}
```

---

### Grille de tarification

#### GET `/api/fleet/pricing/rates`

```json
// Query params: ?category_id=uuid (optionnel)

{
  "data": [
    {
      "id": "uuid",
      "category_id": "uuid-vtt",
      "category_name": "VTT",
      "pricing_class_id": "uuid-standard",
      "pricing_class_label": "Standard",
      "duration_id": "uuid-full-day",
      "duration_label": "Journée",
      "price": 35.00,
      "is_active": true
    }
  ]
}
```

#### PUT `/api/fleet/pricing/rates` (Bulk update)

```json
// Request - Mise à jour en masse de la grille
{
  "rates": [
    {
      "category_id": "uuid-vtt",
      "pricing_class_id": "uuid-standard",
      "duration_id": "uuid-full-day",
      "price": 35.00
    },
    {
      "category_id": "uuid-vtt",
      "pricing_class_id": "uuid-premium",
      "duration_id": "uuid-full-day",
      "price": 50.00
    }
  ]
}

// Response 200
{
  "updated": 25,
  "created": 5
}
```

**Logique backend :**

```php
public function bulkUpdate(BulkUpdateRatesRequest $request)
{
    $tenantId = auth()->user()->tenant_id;

    DB::transaction(function () use ($request, $tenantId) {
        $updated = 0;
        $created = 0;

        foreach ($request->rates as $rateData) {
            $rate = PricingRate::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'category_id' => $rateData['category_id'],
                    'pricing_class_id' => $rateData['pricing_class_id'],
                    'duration_id' => $rateData['duration_id'],
                ],
                [
                    'price' => $rateData['price'],
                    'is_active' => $rateData['is_active'] ?? true,
                ]
            );

            $rate->wasRecentlyCreated ? $created++ : $updated++;
        }

        return compact('updated', 'created');
    });
}
```

---

### Réductions dégressives

#### GET `/api/fleet/pricing/discounts`

```json
{
  "data": [
    {
      "id": "uuid",
      "label": "Réduction longue durée -15%",
      "category_id": null,
      "pricing_class_id": "uuid-premium",
      "min_days": 3,
      "discount_type": "percentage",
      "discount_value": 15,
      "is_cumulative": false,
      "priority": 1,
      "is_active": true
    }
  ]
}
```

#### POST `/api/fleet/pricing/discounts`

```json
// Request
{
  "label": "Réduction semaine -20%",
  "pricing_class_id": null,  // Toutes classes
  "min_days": 7,
  "discount_type": "percentage",
  "discount_value": 20,
  "priority": 2
}
```

---

### Calcul de prix

#### POST `/api/fleet/pricing/calculate`

```json
// Request
{
  "bike_id": "uuid",
  "duration_id": "uuid",
  "custom_days": 4  // Optionnel
}

// Response 200
{
  "base_price": 200.00,
  "final_price": 170.00,
  "days": 4,
  "price_per_day": 50.00,
  "discounts": [
    {
      "label": "Réduction longue durée -15%",
      "type": "percentage",
      "value": 15,
      "amount": 30.00
    }
  ],
  "category_id": "uuid-vtt",
  "pricing_class_id": "uuid-premium",
  "duration_id": "uuid-full-day"
}

// Response 404 (pas de tarif)
{
  "message": "Aucun tarif trouvé pour cette combinaison",
  "options": {
    "can_enter_custom_price": true,
    "can_assign_pricing_class": true
  }
}
```

---

## 🧪 Cas d'usage (Use Cases)

### 1. Créer une nouvelle classe de tarification

```php
namespace Domain\Pricing\UseCases;

class CreatePricingClass
{
    public function execute(CreatePricingClassDTO $dto): PricingClass
    {
        // Validation
        $this->validateUniqueness($dto->tenantId, $dto->code);

        // Création
        $class = PricingClass::create([
            'tenant_id' => $dto->tenantId,
            'code' => Str::slug($dto->code),
            'label' => $dto->label,
            'description' => $dto->description,
            'color' => $dto->color ?? '#3B82F6',
            'sort_order' => $dto->sortOrder ?? $this->getNextSortOrder($dto->tenantId),
        ]);

        // Event
        event(new PricingClassCreated($class));

        return $class;
    }
}
```

---

### 2. Mettre à jour la grille de tarification

```php
namespace Domain\Pricing\UseCases;

class BulkUpdatePricingRates
{
    public function execute(string $tenantId, array $rates): array
    {
        $stats = ['updated' => 0, 'created' => 0];

        DB::transaction(function () use ($tenantId, $rates, &$stats) {
            foreach ($rates as $rateData) {
                // Validation
                $this->validateRate($tenantId, $rateData);

                // Update or Create
                $rate = PricingRate::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'category_id' => $rateData['category_id'],
                        'pricing_class_id' => $rateData['pricing_class_id'],
                        'duration_id' => $rateData['duration_id'],
                    ],
                    [
                        'price' => $rateData['price'],
                    ]
                );

                $rate->wasRecentlyCreated ? $stats['created']++ : $stats['updated']++;
            }
        });

        // Event
        event(new PricingGridUpdated($tenantId));

        return $stats;
    }
}
```

---

### 3. Créer une location avec calcul de prix

```php
namespace Domain\Rentals\UseCases;

class CreateRental
{
    public function __construct(
        private PricingCalculator $pricingCalculator,
        private PricingValidator $pricingValidator
    ) {}

    public function execute(CreateRentalDTO $dto): Rental
    {
        $bike = Bike::findOrFail($dto->bikeId);

        // Validation : vélo louable ?
        if (!$this->pricingValidator->canBikeBeRented($bike)) {
            throw new BikeNotRentableException(
                "Ce vélo n'a pas de classe de tarification ou de tarifs configurés"
            );
        }

        // Calcul du prix
        $calculation = $this->pricingCalculator->calculate(
            tenantId: $bike->tenant_id,
            categoryId: $bike->category_id,
            pricingClassId: $bike->pricing_class_id,
            durationId: $dto->durationId,
            customDays: $dto->customDays
        );

        // Création de la location
        $rental = DB::transaction(function () use ($dto, $calculation) {
            $rental = Rental::create([
                'bike_id' => $dto->bikeId,
                'customer_id' => $dto->customerId,
                'start_date' => $dto->startDate,
                'duration_id' => $dto->durationId,
                'days' => $calculation->days,
                'total_amount' => $calculation->finalPrice,
                // ...
            ]);

            // Snapshot immuable du pricing
            RentalPricingSnapshot::create([
                'rental_id' => $rental->id,
                'tenant_id' => $rental->tenant_id,
                'base_price' => $calculation->basePrice,
                'final_price' => $calculation->finalPrice,
                'discounts_applied' => $calculation->discounts,
                'category_id' => $calculation->categoryId,
                'pricing_class_id' => $calculation->pricingClassId,
                'duration_id' => $calculation->durationId,
                'days' => $calculation->days,
                'price_per_day' => $calculation->pricePerDay,
                'calculated_at' => now(),
            ]);

            return $rental;
        });

        // Event
        event(new RentalCreated($rental));

        return $rental;
    }
}
```

---

## ✅ Validation & Règles métier

### Règles de validation

```php
// CreatePricingClassRequest
public function rules(): array
{
    return [
        'code' => [
            'required',
            'string',
            'max:50',
            'regex:/^[a-z0-9_]+$/',
            Rule::unique('pricing_classes')->where('tenant_id', auth()->user()->tenant_id),
        ],
        'label' => 'required|string|max:100',
        'description' => 'nullable|string|max:500',
        'color' => 'nullable|regex:/^#[0-9A-F]{6}$/i',
        'sort_order' => 'nullable|integer|min:0',
    ];
}

// CreateDurationRequest
public function rules(): array
{
    return [
        'code' => [
            'required',
            'string',
            'max:50',
            'regex:/^[a-z0-9_]+$/',
            Rule::unique('duration_definitions')->where('tenant_id', auth()->user()->tenant_id),
        ],
        'label' => 'required|string|max:100',
        'duration_hours' => 'nullable|integer|min:1|required_without:duration_days',
        'duration_days' => 'nullable|integer|min:1|required_without:duration_hours',
        'sort_order' => 'nullable|integer|min:0',
    ];
}

// BulkUpdateRatesRequest
public function rules(): array
{
    return [
        'rates' => 'required|array|min:1',
        'rates.*.category_id' => 'required|exists:categories,id',
        'rates.*.pricing_class_id' => 'required|exists:pricing_classes,id',
        'rates.*.duration_id' => 'required|exists:duration_definitions,id',
        'rates.*.price' => 'required|numeric|min:0.01',
    ];
}

// CreateDiscountRequest
public function rules(): array
{
    return [
        'label' => 'required|string|max:100',
        'category_id' => 'nullable|exists:categories,id',
        'pricing_class_id' => 'nullable|exists:pricing_classes,id',
        'min_days' => 'nullable|integer|min:1|required_without:min_duration_id',
        'min_duration_id' => 'nullable|exists:duration_definitions,id|required_without:min_days',
        'discount_type' => 'required|in:percentage,fixed',
        'discount_value' => [
            'required',
            'numeric',
            'min:0.01',
            function ($attribute, $value, $fail) {
                if ($this->discount_type === 'percentage' && $value > 100) {
                    $fail('La réduction en pourcentage ne peut pas dépasser 100%');
                }
            },
        ],
        'priority' => 'nullable|integer|min:0',
    ];
}
```

---

## 🔒 Permissions

```php
// Policy : PricingPolicy
public function viewPricing(User $user): bool
{
    return $user->hasPermission('view_bikes');
}

public function managePricing(User $user): bool
{
    return $user->hasPermission('manage_bikes');
}

// Dans les controllers
public function __construct()
{
    $this->authorizeResource(PricingClass::class, 'pricing_class');
}
```

---

## 📦 Migration des données existantes

```php
namespace Database\Seeders;

class MigratePricingDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer classe "Standard" par défaut pour chaque tenant
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $standardClass = PricingClass::create([
                'tenant_id' => $tenant->id,
                'code' => 'standard',
                'label' => 'Standard',
                'description' => 'Classe de tarification par défaut',
                'color' => '#3B82F6',
                'sort_order' => 1,
            ]);

            // 2. Migrer les anciens tarifs (par catégorie uniquement)
            $oldRates = Rate::where('tenant_id', $tenant->id)->get();

            foreach ($oldRates as $oldRate) {
                // Mapper ancienne durée vers nouvelle
                $duration = $this->mapOldDurationToNew($tenant->id, $oldRate->duration);

                if ($duration) {
                    PricingRate::create([
                        'tenant_id' => $tenant->id,
                        'category_id' => $oldRate->category_id,
                        'pricing_class_id' => $standardClass->id,
                        'duration_id' => $duration->id,
                        'price' => $oldRate->price,
                    ]);
                }
            }

            // 3. Assigner classe "Standard" à tous les vélos du tenant
            Bike::where('tenant_id', $tenant->id)
                ->whereNull('pricing_class_id')
                ->update(['pricing_class_id' => $standardClass->id]);
        }
    }
}
```

---

## 🚀 Données de seed (pour développement)

```php
namespace Database\Seeders;

class PricingSeedSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        // Classes
        $standard = PricingClass::create([
            'tenant_id' => $tenant->id,
            'code' => 'standard',
            'label' => 'Standard',
            'color' => '#3B82F6',
            'sort_order' => 1,
        ]);

        $premium = PricingClass::create([
            'tenant_id' => $tenant->id,
            'code' => 'premium',
            'label' => 'Premium',
            'color' => '#8B5CF6',
            'sort_order' => 2,
        ]);

        // Durées
        $halfDay = DurationDefinition::create([
            'tenant_id' => $tenant->id,
            'code' => 'half_day',
            'label' => 'Demi-journée',
            'duration_hours' => 4,
            'sort_order' => 1,
        ]);

        $fullDay = DurationDefinition::create([
            'tenant_id' => $tenant->id,
            'code' => 'full_day',
            'label' => 'Journée',
            'duration_days' => 1,
            'sort_order' => 2,
        ]);

        // Grille de tarification (exemple)
        $vtt = Category::where('slug', 'vtt')->first();

        PricingRate::create([
            'tenant_id' => $tenant->id,
            'category_id' => $vtt->id,
            'pricing_class_id' => $standard->id,
            'duration_id' => $fullDay->id,
            'price' => 35.00,
        ]);

        // Réduction dégressive
        DiscountRule::create([
            'tenant_id' => $tenant->id,
            'pricing_class_id' => $premium->id,
            'min_days' => 3,
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'label' => 'Réduction longue durée Premium -15%',
            'priority' => 1,
        ]);
    }
}
```

---

## 📝 Checklist d'implémentation

### Backend
- [ ] Créer les migrations (5 tables + alter bikes)
- [ ] Créer les modèles Eloquent
- [ ] Créer les DTOs (Data Transfer Objects)
- [ ] Créer les Value Objects (PriceCalculation, etc.)
- [ ] Implémenter PricingCalculator (service)
- [ ] Implémenter PricingValidator (service)
- [ ] Créer les Use Cases
- [ ] Créer les Controllers
- [ ] Créer les Form Requests (validation)
- [ ] Créer les Policies (permissions)
- [ ] Créer les Resources (API responses)
- [ ] Créer les Events
- [ ] Créer le seeder de migration
- [ ] Créer le seeder de données de dev
- [ ] Écrire les tests unitaires
- [ ] Écrire les tests d'intégration
- [ ] Documenter l'OpenAPI

### Frontend (à faire après)
- [ ] Créer les types TypeScript
- [ ] Créer les fonctions API client
- [ ] Créer les hooks React Query
- [ ] Créer PricingClassesPage
- [ ] Créer DurationsPage
- [ ] Créer PricingGridPage (grille 3D)
- [ ] Créer DiscountRulesPage
- [ ] Intégrer dans BikeDetailPage
- [ ] Intégrer dans NewRentalPage
- [ ] Tester end-to-end

---

## 🎓 Bonnes pratiques DDD

- **Aggregate Roots** : `PricingConfiguration` comme racine
- **Value Objects** : `PriceCalculation`, `Money`, `Discount`
- **Domain Events** : `PricingClassCreated`, `PricingGridUpdated`
- **Repository Pattern** : `PricingRateRepository`, `DiscountRuleRepository`
- **Domain Services** : `PricingCalculator`, `PricingValidator`
- **Use Cases** : Un fichier par action métier
- **Immutabilité** : `RentalPricingSnapshot` immuable après création

---

**Document créé le :** 2026-02-06
**Version :** 1.0 (MVP)
**Prochaine révision :** Phase 2 (Cascade App/Site/Vélo)
