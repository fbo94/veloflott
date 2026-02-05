# Commandes Composer Disponibles

## 🧪 Tests (Pest)

```bash
composer test              # Lancer tous les tests
composer test:unit         # Tests unitaires uniquement
composer test:feature      # Tests feature uniquement
composer test:coverage     # Tests avec couverture de code
composer test:parallel     # Tests en parallèle (plus rapide)
```

**Exemples :**
```bash
# Dans Docker
docker exec php-api composer test

# En local
composer test
```

---

## 🎨 Code Style (PHPCS)

```bash
composer cs                # Vérifier le style de code (PSR-12)
composer cs:fix            # Corriger automatiquement les erreurs de style
```

**Exemples :**
```bash
# Vérifier tout le code
composer cs

# Corriger automatiquement
composer cs:fix

# Dans Docker
docker exec php-api composer cs:fix
```

---

## 🔍 Analyse Statique (PHPStan)

```bash
composer stan              # Analyser le code (niveau 7)
composer stan:baseline     # Générer une nouvelle baseline
```

**Exemples :**
```bash
# Analyser le code
composer stan

# Créer une baseline des erreurs existantes
composer stan:baseline

# Dans Docker
docker exec php-api composer stan
```

---

## ✅ Qualité Globale

```bash
composer lint              # PHPCS + PHPStan
composer quality           # Lint + Tests (tout vérifier)
composer quality:fix       # Corriger le style + analyser + tester
```

**Exemples :**
```bash
# Avant chaque commit
composer quality

# Corriger et vérifier
composer quality:fix

# Dans Docker
docker exec php-api composer quality
```

---

## 💾 Migrations de Base de Données

```bash
composer migrate           # Exécuter les migrations
composer migrate:fresh     # Supprimer toutes les tables et migrer
composer migrate:refresh   # Rollback toutes les migrations et re-migrer
composer migrate:rollback  # Annuler la dernière migration
composer migrate:status    # Voir l'état des migrations
```

**Exemples :**
```bash
# Appliquer les nouvelles migrations
composer migrate

# Reset complet de la BDD
composer migrate:fresh

# Dans Docker
docker exec php-api composer migrate
```

---

## 🌱 Seeders

```bash
composer db:seed           # Exécuter les seeders
composer db:fresh          # Migration fresh + seeders
```

**Exemples :**
```bash
# Remplir la BDD avec des données de test
composer db:seed

# Reset complet + données de test
composer db:fresh

# Dans Docker
docker exec php-api composer db:fresh
```

---

## 🧹 Cache

```bash
composer cache:clear-all   # Vider tous les caches (config, routes, views, cache)
composer optimize          # Optimiser (mettre en cache config, routes, views)
```

**Exemples :**
```bash
# Après modification de .env ou routes
composer cache:clear-all

# Avant déploiement en production
composer optimize

# Dans Docker
docker exec php-api composer cache:clear-all
```

---

## 🚀 Installation & Développement

```bash
composer setup             # Installation complète du projet
composer dev               # Lancer le serveur de développement
```

**Exemples :**
```bash
# Premier setup du projet
composer setup

# Lancer l'environnement de dev
composer dev
```

---

## 📋 Workflows Recommandés

### Avant chaque commit
```bash
composer quality:fix
# ou
composer cs:fix && composer quality
```

### Après avoir tiré du code (git pull)
```bash
composer install
composer migrate
composer cache:clear-all
```

### Avant de créer une PR
```bash
composer quality           # Vérifier tout
composer test:coverage     # Vérifier la couverture
```

### Reset complet de l'environnement
```bash
composer db:fresh          # Reset BDD avec données
composer cache:clear-all   # Vider les caches
```

### Développement d'une nouvelle feature
```bash
# 1. Créer la migration
php artisan make:migration create_something_table

# 2. Exécuter la migration
composer migrate

# 3. Développer...

# 4. Tester
composer test:unit

# 5. Vérifier la qualité
composer quality
```

---

## 🐳 Commandes Docker Équivalentes

```bash
# Tests
docker exec php-api composer test
docker exec php-api composer test:unit

# Style
docker exec php-api composer cs
docker exec php-api composer cs:fix

# Analyse
docker exec php-api composer stan

# Qualité
docker exec php-api composer quality

# Migrations
docker exec php-api composer migrate
docker exec php-api composer db:fresh

# Cache
docker exec php-api composer cache:clear-all
```

---

## 💡 Tips

1. **Aliases Bash** - Ajoutez ces aliases dans votre `~/.bashrc` ou `~/.zshrc` :
```bash
alias dcomposer='docker exec php-api composer'
alias dphp='docker exec php-api php'
alias dartisan='docker exec php-api php artisan'
```

Puis utilisez :
```bash
dcomposer test
dartisan migrate
```

2. **Git Hooks** - Créez un pre-commit hook pour vérifier la qualité :
```bash
#!/bin/bash
# .git/hooks/pre-commit
docker exec php-api composer quality
```

3. **Watch Mode** - Pour relancer les tests automatiquement :
```bash
docker exec php-api ./vendor/bin/pest --watch
```

4. **Filtrer les tests** :
```bash
docker exec php-api ./vendor/bin/pest --filter=RentalStatus
docker exec php-api ./vendor/bin/pest tests/Unit/Rental/
```

---

## 📊 Statistiques

Voir les statistiques de votre projet :

```bash
# Nombre de tests
vendor/bin/pest --list-tests | wc -l

# Couverture de code
composer test:coverage

# Statistiques PHPCS
composer cs -- --report=summary

# Statistiques PHPStan
composer stan -- --error-format=table
```
