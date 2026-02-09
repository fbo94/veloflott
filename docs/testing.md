# Testing & Code Quality

## Pest (Testing Framework)

Pest est un framework de test moderne pour PHP, construit sur PHPUnit.

### Lancer les tests

```bash
# Tous les tests
composer test

# Tests unitaires uniquement
composer test:unit

# Tests feature uniquement
composer test:feature

# Avec couverture de code
composer test:coverage

# Depuis Docker
docker exec php-api composer test
```

### Structure des tests

```
tests/
├── Pest.php              # Configuration Pest
├── TestCase.php          # Classe de base pour les tests
├── Unit/                 # Tests unitaires (logique métier pure)
│   ├── ExampleTest.php
│   └── Rental/
│       └── RentalStatusTest.php
└── Feature/              # Tests d'intégration (API, base de données)
    └── ExampleTest.php
```

### Écrire un test

```php
<?php

declare(strict_types=1);

use Rental\Domain\RentalStatus;

test('pending rental can start', function () {
    $status = RentalStatus::PENDING;
    
    expect($status->canStart())->toBeTrue();
    expect($status->canCheckOut())->toBeFalse();
});
```

### Tests paramétrés

```php
test('rental statuses', function (RentalStatus $status, bool $canStart) {
    expect($status->canStart())->toBe($canStart);
})->with([
    [RentalStatus::PENDING, true],
    [RentalStatus::ACTIVE, false],
    [RentalStatus::COMPLETED, false],
]);
```

## PHPCS (PHP CodeSniffer)

PHP CodeSniffer vérifie que le code respecte les standards PSR-12.

### Vérifier le code

```bash
# Vérifier tout le code
composer cs

# Vérifier un fichier spécifique
./vendor/bin/phpcs src/Rental/Domain/Rental.php

# Vérifier un dossier
./vendor/bin/phpcs src/Rental/

# Depuis Docker
docker exec php-api composer cs
```

### Corriger automatiquement

```bash
# Corriger automatiquement les problèmes
composer cs:fix

# Corriger un fichier spécifique
./vendor/bin/phpcbf src/Rental/Domain/Rental.php

# Depuis Docker
docker exec php-api composer cs:fix
```

### Règles de codage

Le projet utilise :
- **PSR-12** : Standard de codage PHP moderne
- **Strict types** : Déclaration obligatoire `declare(strict_types=1);`
- **Limite de ligne** : 120 caractères (warning à 150)
- **Interdictions** : `var_dump`, `dd`, `dump`, `print`

### Configuration

La configuration est dans `phpcs.xml.dist`. Vous pouvez créer un `phpcs.xml` local pour vos préférences personnelles (non versionné).

## Commandes Utiles

```bash
# Vérifier la qualité du code (lint + tests)
composer quality

# Linter uniquement
composer lint

# Tests en mode watch (relance automatiquement)
./vendor/bin/pest --watch

# Tests avec filtre
./vendor/bin/pest --filter=RentalStatus
```

## CI/CD

Pour intégrer dans votre pipeline CI/CD :

```yaml
test:
  script:
    - composer install
    - composer cs
    - composer test
```

## Bonnes Pratiques

1. **Tests unitaires** : Testez la logique métier du Domain (Value Objects, Entities, Services)
2. **Tests feature** : Testez les endpoints API et l'intégration avec la base de données
3. **Couverture** : Visez au moins 80% de couverture pour le Domain
4. **Nommage** : Utilisez des noms descriptifs pour les tests (pas de `test_method_name`)
5. **Arrange-Act-Assert** : Structurez vos tests en 3 parties
6. **Un concept par test** : Ne testez qu'une seule chose à la fois

## Exemples

### Test d'une Value Object

```php
test('frame size can be created from request', function () {
    $frameSize = FrameSize::fromRequest('numeric', 54.0, null);
    
    expect($frameSize->unit())->toBe(FrameSizeUnit::NUMERIC);
    expect($frameSize->numericValue())->toBe(54.0);
    expect($frameSize->letterValue())->toBeNull();
});
```

### Test d'un Handler

```php
test('get bike rentals handler filters by status', function () {
    // Arrange
    $repository = Mockery::mock(RentalRepositoryInterface::class);
    $handler = new GetBikeRentalsHandler($repository);
    
    $repository->shouldReceive('findByBikeId')
        ->once()
        ->with('bike-id', [RentalStatus::ACTIVE])
        ->andReturn([]);
    
    // Act
    $query = new GetBikeRentalsQuery('bike-id', 'current');
    $response = $handler->handle($query);
    
    // Assert
    expect($response->totalCount)->toBe(0);
});
```

### Test d'une API

```php
test('get bike rentals endpoint returns filtered results', function () {
    $bike = Bike::factory()->create();
    Rental::factory()->active()->create(['bike_id' => $bike->id]);
    
    $response = $this->getJson("/api/rentals/bikes/{$bike->id}?filter=current");
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'bike_id',
            'total_count',
            'rentals' => [
                '*' => ['id', 'status', 'start_date']
            ]
        ]);
});
```

## PHPStan (Analyse Statique)

PHPStan analyse le code sans l'exécuter pour trouver les bugs avant qu'ils n'arrivent en production.

### Lancer l'analyse

```bash
# Analyser tout le code
composer stan

# Générer une nouvelle baseline (ignorer les erreurs existantes)
composer stan:baseline

# Analyser directement avec PHPStan
./vendor/bin/phpstan analyse

# Depuis Docker
docker exec php-api composer stan
```

### Configuration

La configuration est dans `phpstan.neon` :
- **Niveau 6** : Niveau d'analyse strict (0-9, 9 étant le plus strict)
- **Larastan** : Extension pour Laravel avec règles spécifiques
- **Baseline** : Les erreurs existantes sont ignorées (`phpstan-baseline.neon`)

### Niveau d'analyse

PHPStan a 10 niveaux (0-9) :
- **Niveau 0-3** : Vérifications basiques (undefined variables, unknown functions)
- **Niveau 4-6** : Type checking avancé (notre niveau actuel)
- **Niveau 7-9** : Vérifications ultra-strictes (mixed types, etc.)

### Types d'erreurs détectées

```php
// ❌ Mauvais : Type non spécifié
public function getItems(): array
{
    return $this->items;
}

// ✅ Bon : Type spécifié
/**
 * @return Item[]
 */
public function getItems(): array
{
    return $this->items;
}

// ❌ Mauvais : Return manquant
public function calculate(): int
{
    if ($condition) {
        return 42;
    }
    // Manque un return ici!
}

// ✅ Bon : Tous les cas couverts
public function calculate(): int
{
    if ($condition) {
        return 42;
    }
    throw new \InvalidArgumentException('Invalid condition');
}
```

### Baseline

La baseline permet d'ignorer les erreurs existantes et de se concentrer sur le nouveau code :

```bash
# Générer une nouvelle baseline
composer stan:baseline

# Ensuite, PHPStan ne signalera que les NOUVELLES erreurs
composer stan
```

**Important** : Ne regénérez la baseline que si vous avez vraiment besoin d'ignorer des erreurs existantes. L'objectif est de corriger les erreurs, pas de les ignorer !

### Intégration IDE

PHPStan peut être intégré dans votre IDE pour voir les erreurs en temps réel :

**PHPStorm** :
1. Settings → PHP → Quality Tools → PHPStan
2. Configuration file: `phpstan.neon`
3. Activer l'inspection PHPStan

**VSCode** :
1. Installer l'extension "PHPStan"
2. Configurer le chemin vers phpstan dans les settings

