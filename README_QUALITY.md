# Outils de Qualité de Code

Ce projet utilise trois outils complémentaires pour assurer la qualité du code :

## 🧪 Pest - Tests

Framework de tests moderne pour PHP.

```bash
composer test              # Tous les tests
composer test:unit         # Tests unitaires
composer test:feature      # Tests d'intégration
composer test:coverage     # Avec couverture
```

**Résultats actuels :** ✅ 7/7 tests unitaires passent

## 🎨 PHPCS - Style de Code

Vérifie que le code respecte PSR-12.

```bash
composer cs                # Vérifier
composer cs:fix            # Corriger automatiquement
```

**Standards :**
- PSR-12
- Strict types obligatoire
- Max 120 caractères/ligne
- Interdit : var_dump, dd, dump

## 🔍 PHPStan - Analyse Statique

Détecte les bugs avant l'exécution (niveau 6/9).

```bash
composer stan              # Analyser
composer stan:baseline     # Créer baseline
```

**Détecte :**
- Types manquants
- Returns manquants
- Appels de méthodes invalides
- Code inaccessible

## 🚀 Tout Vérifier

```bash
composer quality           # PHPCS + PHPStan + Pest
composer lint              # PHPCS + PHPStan uniquement
```

## 📁 Fichiers de Configuration

- `phpunit.xml` - Configuration PHPUnit/Pest
- `tests/Pest.php` - Configuration Pest
- `phpcs.xml.dist` - Règles PHPCS
- `phpstan.neon` - Configuration PHPStan
- `phpstan-baseline.neon` - Erreurs existantes ignorées

## 🐳 Dans Docker

```bash
docker exec php-api composer test
docker exec php-api composer cs
docker exec php-api composer stan
docker exec php-api composer quality
```

## 📚 Documentation Complète

Voir `/docs/testing.md` pour :
- Guide détaillé de Pest
- Exemples de tests
- Configuration PHPCS
- Utilisation de PHPStan
- Bonnes pratiques
- Intégration CI/CD

## ✨ Bonnes Pratiques

1. **Avant chaque commit :** `composer quality`
2. **Tests :** Minimum 1 test par feature
3. **Style :** Corriger avec `composer cs:fix`
4. **Types :** Toujours typer les paramètres et retours
5. **Baseline :** Ne pas regénérer sans raison

## 🎯 Objectifs

- ✅ Couverture de tests : 80% minimum sur le Domain
- ✅ Zéro erreur PHPCS
- ✅ Zéro erreur PHPStan (hors baseline)
- ✅ Strict types partout
