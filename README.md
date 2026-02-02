# Fleet Manager - VeloFlott

Application SaaS de gestion de flotte de vélos pour loueurs premium.

---

## 🎯 Présentation

Fleet Manager permet aux professionnels de la location de vélos de gérer :

- **Flotte** : Vélos, catégories, marques, tarifs
- **Locations** : Réservations, check-in/out, facturation
- **Clients** : Fichier client, historique, documents
- **Maintenance** : Interventions, planification
- **Dashboard** : Statistiques, indicateurs

---

## 🛠️ Stack technique

| Composant | Technologie                      |
|-----------|----------------------------------|
| Backend | PHP 8.4 / Laravel 12             |
| Base de données | PostgreSQL 15                    |
| Authentification | Keycloak (OAuth2/OIDC)           |
| Cache | Redis                            |
| Architecture | DDD Modulaire (Bounded Contexts) |
| API | REST JSON                        |

---

## 🚀 Installation rapide

### Prérequis

- Docker & Docker Compose
- PHP 8.4+ (pour développement local)
- Composer 2+

### Démarrage

```bash
# Cloner le projet
git clone git@github.com:fbo94/veloflott.git
cd veloflott

# Copier la configuration
cp .env.example .env

# Lancer l'environnement Docker
docker-compose up -d

# Installer les dépendances
composer install

# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate

# (Optionnel) Seed les données de test
php artisan db:seed
```

### Accès aux services

| Service | URL                                  | Credentials |
|---------|--------------------------------------|-------------|
| API | https://veloflott.localhost:8000/api | - |
| Keycloak Admin | https://keycloak.localhost:8080      | admin / admin |
| PostgreSQL | localhost:5432                       | veloflott_user / password |

---

## 🔐 Authentification

L'application utilise **Keycloak** pour l'authentification via OAuth2/OIDC.

### Configuration Keycloak

1. **Accéder à Keycloak Admin** : https://keycloak.localhost:8080
2. **Créer le realm** : `veloflott`
3. **Créer le client** : `veloflott-api`
   - Client Type: `OpenID Connect`
   - Access Type: `confidential`
   - Valid Redirect URIs: `https://veloflott.localhost/api/auth/callback`
   - Web Origins: `https://veloflott.localhost`
4. **Récupérer le Client Secret** et l'ajouter dans `.env`

### Flow d'authentification OAuth2

```
┌─────────┐                                  ┌──────────┐
│ Client  │                                  │   API    │
│  (App)  │                                  │ Laravel  │
└────┬────┘                                  └────┬─────┘
     │                                            │
     │  1. GET /api/auth/authorization-url       │
     │──────────────────────────────────────────>│
     │                                            │
     │  Returns: {authorization_url, state}      │
     │<──────────────────────────────────────────│
     │                                            │
     │  2. Redirect to Keycloak                  │
     │────────────────────────┐                  │
     │                        │                  │
     │                   ┌────▼─────┐            │
     │                   │ Keycloak │            │
     │                   │  Login   │            │
     │                   └────┬─────┘            │
     │                        │                  │
     │  3. Callback: ?code=xxx&state=xxx        │
     │<───────────────────────┘                  │
     │                                            │
     │  4. POST /api/auth/authorize              │
     │     {code, state}                         │
     │──────────────────────────────────────────>│
     │                                            │
     │  Returns: {access_token, refresh_token}   │
     │<──────────────────────────────────────────│
     │                                            │
     │  5. API calls with Bearer token           │
     │     Authorization: Bearer {token}         │
     │──────────────────────────────────────────>│
     │                                            │
```

### Endpoints d'authentification

#### 1. Obtenir l'URL d'autorisation

```bash
GET /api/auth/authorization-url
```

**Réponse :**
```json
{
  "authorization_url": "https://keycloak.localhost:8080/realms/veloflott/protocol/openid-connect/auth?...",
  "state": "random_csrf_token"
}
```

#### 2. Échanger le code contre un token

```bash
POST /api/auth/authorize
Content-Type: application/json

{
  "code": "authorization_code_from_keycloak",
  "state": "random_csrf_token"
}
```

**Réponse (succès) :**
```json
{
  "access_token": "eyJhbGciOiJSUzI1NiIs...",
  "refresh_token": "eyJhbGciOiJIUzI1NiIs...",
  "expires_in": 3600,
  "token_type": "Bearer",
  "user": {
    "id": "uuid",
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "role": "employee",
    "role_label": "Employé"
  }
}
```

---

## 📡 Endpoints API

### Authentification (Public)

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/auth/authorization-url` | Obtenir l'URL d'autorisation Keycloak |
| POST | `/api/auth/authorize` | Échanger le code contre un token |

### Utilisateur (Authentifié)

| Méthode | Endpoint | Permission | Description |
|---------|----------|------------|-------------|
| GET | `/api/me` | - | Récupérer l'utilisateur courant |

### Gestion des utilisateurs (Admin)

| Méthode | Endpoint | Permission | Description |
|---------|----------|------------|-------------|
| GET | `/api/users` | `manage_users` | Lister les utilisateurs |
| PUT | `/api/users/{id}/role` | `manage_users` | Modifier le rôle d'un utilisateur |
| POST | `/api/users/{id}/toggle-status` | `manage_users` | Activer/Désactiver un utilisateur |

### Exemple d'utilisation

```bash
# 1. Obtenir l'URL d'autorisation
curl http://localhost/api/auth/authorization-url

# 2. Rediriger l'utilisateur vers l'URL retournée
# L'utilisateur se connecte sur Keycloak

# 3. Échanger le code reçu contre un token
curl -X POST http://localhost/api/auth/authorize \
  -H "Content-Type: application/json" \
  -d '{"code": "xxx", "state": "yyy"}'

# 4. Appeler l'API avec le token
curl -H "Authorization: Bearer {access_token}" \
  http://localhost/api/me
```

---

## 👥 Rôles et permissions

### Rôles disponibles

| Rôle | Description |
|------|-------------|
| **Admin** | Accès complet à toutes les fonctionnalités |
| **Manager** | Gestion opérationnelle (flotte, locations, clients, stats) |
| **Employee** | Opérations courantes (consultations, créations) |

### Matrice des permissions

| Permission | Admin | Manager | Employee |
|------------|-------|---------|----------|
| **Fleet** | | | |
| `view_bikes` | ✅ | ✅ | ✅ |
| `manage_bikes` | ✅ | ✅ | ❌ |
| `delete_bikes` | ✅ | ❌ | ❌ |
| `manage_categories` | ✅ | ❌ | ❌ |
| `manage_rates` | ✅ | ✅ | ❌ |
| **Rental** | | | |
| `view_rentals` | ✅ | ✅ | ✅ |
| `create_rentals` | ✅ | ✅ | ✅ |
| `cancel_rentals` | ✅ | ✅ | ❌ |
| **Customer** | | | |
| `view_customers` | ✅ | ✅ | ✅ |
| `manage_customers` | ✅ | ✅ | ✅ |
| `delete_customers` | ✅ | ❌ | ❌ |
| **Maintenance** | | | |
| `view_maintenances` | ✅ | ✅ | ✅ |
| `create_maintenances` | ✅ | ✅ | ✅ |
| `close_maintenances` | ✅ | ✅ | ❌ |
| **Dashboard** | | | |
| `view_stats` | ✅ | ✅ | ❌ |
| **Users** | | | |
| `manage_users` | ✅ | ❌ | ❌ |

---

## 📁 Structure du projet

### Architecture DDD Modulaire

```
src/
├── Auth/                       # Module Authentification
│   ├── Domain/                 # Entités, Value Objects, Interfaces
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── RolePermissions.php
│   │   └── UserRepositoryInterface.php
│   │
│   ├── Application/            # Use Cases
│   │   ├── GetCurrentUser/
│   │   ├── ListUsers/
│   │   ├── UpdateUserRole/
│   │   ├── ToggleUserStatus/
│   │   ├── GetAuthorizationUrl/
│   │   └── Authorize/
│   │
│   ├── Infrastructure/         # Implémentations
│   │   ├── Keycloak/
│   │   │   ├── KeycloakTokenValidator.php
│   │   │   ├── KeycloakOAuthService.php
│   │   │   └── UserSyncService.php
│   │   ├── Persistence/
│   │   │   ├── EloquentUserRepository.php
│   │   │   └── Models/UserEloquentModel.php
│   │   ├── AuthServiceProvider.php
│   │   └── migrations/
│   │
│   └── Interface/              # Points d'entrée
│       └── Http/
│           ├── Middleware/
│           │   ├── KeycloakAuthenticate.php
│           │   └── CheckPermission.php
│           ├── GetCurrentUser/
│           ├── ListUsers/
│           ├── UpdateUserRole/
│           ├── ToggleUserStatus/
│           ├── GetAuthorizationUrl/
│           ├── Authorize/
│           └── routes.php
│
├── Fleet/                      # Module Flotte (à venir)
├── Rental/                     # Module Locations (à venir)
├── Customer/                   # Module Clients (à venir)
├── Maintenance/                # Module Maintenance (à venir)
│
└── Shared/                     # Kernel partagé
    ├── Domain/
    │   └── DomainException.php
    ├── Application/
    └── Infrastructure/
```

### Principes architecturaux

#### Couches DDD

```
┌─────────────────────────────────────────┐
│           Interface                     │
│      (Controllers, Requests)            │
│      Dépend de: Application             │
└─────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│          Application                    │
│     (Commands, Queries, Handlers)       │
│      Dépend de: Domain uniquement       │
└─────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│            Domain                       │
│    (Entités, Value Objects, Rules)      │
│         AUCUNE dépendance               │
└─────────────────────────────────────────┘
                  ▲
                  │
┌─────────────────────────────────────────┐
│        Infrastructure                   │
│   (Repositories, Services, Adapters)    │
│   Implémente les interfaces du Domain   │
└─────────────────────────────────────────┘
```

#### Règles strictes

- **Domain** : Pur PHP, aucune dépendance framework
- **Application** : Dépend uniquement du Domain
- **Infrastructure** : Implémente les contrats du Domain
- **Interface** : Point d'entrée, dépend de Application

---

## 🧪 Tests

### Exécution des tests

```bash
# Tous les tests
composer test

# Tests unitaires uniquement
./vendor/bin/phpunit --testsuite=Unit

# Tests feature uniquement
./vendor/bin/phpunit --testsuite=Feature

# Avec coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Structure des tests

```
tests/
├── Unit/
│   └── Auth/
│       ├── Domain/
│       │   ├── RoleTest.php
│       │   ├── PermissionTest.php
│       │   └── RolePermissionsTest.php
│       └── Application/
│           └── UpdateUserRole/
│               └── UpdateUserRoleHandlerTest.php
│
├── Feature/
│   └── Auth/
│       ├── KeycloakAuthenticateTest.php
│       ├── CheckPermissionTest.php
│       └── GetCurrentUserControllerTest.php
│
└── Integration/
    └── AuthFlowTest.php
```

---

## 🔧 Commandes utiles

### Développement

```bash
# Accès au container PHP
docker-compose exec app bash

# Lancer les migrations
php artisan migrate

# Rollback des migrations
php artisan migrate:rollback

# Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Regénérer l'autoload
composer dump-autoload
```

### Qualité de code

```bash
# Analyse statique (PHPStan niveau 8)
./vendor/bin/phpstan analyse src --level=8

# Linter (PHP CS Fixer)
./vendor/bin/php-cs-fixer fix src --dry-run --diff

# Corriger le style
./vendor/bin/php-cs-fixer fix src

# Audit de sécurité
composer audit

# Tout vérifier
composer check
```

### Base de données

```bash
# Créer une migration
php artisan make:migration create_bikes_table

# Créer un seeder
php artisan make:seeder BikeSeeder

# Seed la base
php artisan db:seed

# Reset + migrate + seed
php artisan migrate:fresh --seed
```

### Keycloak

```bash
# Vider le cache des clés JWKS
php artisan tinker
>>> app(\Auth\Infrastructure\Keycloak\KeycloakTokenValidator::class)->clearCache()
```

---

## 📚 Documentation

- **[Conventions de développement](CONVENTIONS.md)** - Architecture DDD, SOLID, KISS
- **[Epic 0 - Auth Instructions](EPIC-0-AUTH-INSTRUCTIONS.md)** - Détails techniques de l'authentification

### Conventions principales

- **1 classe = 1 responsabilité** (SRP)
- **1 fichier = 1 classe**
- **Single Action Controllers** (`__invoke` uniquement)
- **Organisation par Use Case** (pas par type)
- **Typage strict** (`declare(strict_types=1)`)
- **Classes `final` par défaut**
- **Exceptions métier** avec codes explicites

---

## 🐛 Débogage

### Logs

```bash
# Suivre les logs en temps réel
tail -f storage/logs/laravel.log

# Logs Docker
docker-compose logs -f app
```

### Variables d'environnement importantes

```env
# Application
APP_DEBUG=true
LOG_LEVEL=debug

# Base de données
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=veloflott_db
DB_USERNAME=veloflott_user
DB_PASSWORD=password

# Keycloak
KEYCLOAK_URL=https://keycloak.localhost:8080
KEYCLOAK_REALM=veloflott
KEYCLOAK_CLIENT_ID=veloflott-api
KEYCLOAK_CLIENT_SECRET=your_client_secret_here
KEYCLOAK_REDIRECT_URI=http://localhost/api/auth/authorization-url
```

---

## 🚧 Roadmap

### ✅ Phase 1 - MVP (Actuel)

- [x] Architecture DDD modulaire
- [x] Module Auth (Keycloak OAuth2)
- [x] Gestion des utilisateurs
- [x] Rôles et permissions
- [x] Exception handling global

### 🔜 Phase 2 - Core Features

- [ ] Module Fleet (vélos, catégories, marques)
- [ ] Module Rental (locations, réservations)
- [ ] Module Customer (clients, documents)
- [ ] Module Maintenance (interventions)

### 🔮 Phase 3 - Advanced

- [ ] Dashboard & Analytics
- [ ] Notifications (email, SMS)
- [ ] Export de données (PDF, Excel)
- [ ] API webhooks

---

## 🤝 Contribution

### Workflow Git

```bash
# Créer une branche feature
git checkout -b feature/fleet-add-bike-model

# Commits conventionnels
git commit -m "feat(fleet): add BikeModel entity"
git commit -m "fix(auth): correct permission check"
git commit -m "test(rental): add RentBikeHandler tests"

# Push et Pull Request
git push origin feature/fleet-add-bike-model
```

### Checklist avant PR

- [ ] `composer check` passe (analyse + lint + tests)
- [ ] Tests du use case présents
- [ ] Pas de dépendance cross-module directe
- [ ] Documentation à jour
- [ ] Pas de `dd()` ou `var_dump()` oubliés

---

## 📄 Licence

Propriétaire - Tous droits réservés.

© 2026 VeloFlott - Fleet Manager
