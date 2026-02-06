# Guide des Migrations - Déploiement GCP

Ce document explique comment gérer les migrations de base de données dans l'environnement de production GCP.

## 📋 Vue d'ensemble

Les migrations de base de données sont **automatiquement exécutées** lors du déploiement via Cloud Build. Le processus est le suivant :

1. ✅ Build de l'image Docker
2. ✅ Push de l'image vers Container Registry
3. ✅ **Exécution des migrations** (nouvellement activé)
4. ✅ Déploiement sur Cloud Run

## 🔄 Migrations Automatiques (Déploiement)

Les migrations s'exécutent automatiquement lors de chaque déploiement via Cloud Build grâce à la configuration dans `cloudbuild.yaml`.

### Configuration

```yaml
# Step 3: Run database migrations
- name: 'gcr.io/google-appengine/exec-wrapper'
  args:
    - '-i'
    - 'gcr.io/$PROJECT_ID/veloflott-api:$BUILD_ID'
    - '-s'
    - '$_CLOUD_SQL_CONNECTION_NAME'
    - '--'
    - 'php'
    - 'artisan'
    - 'migrate'
    - '--force'
    - '--no-interaction'
  id: 'run-migrations'
  waitFor: ['push-latest']
```

### Ordre d'exécution

Le déploiement attend que les migrations soient **complétées avec succès** avant de déployer la nouvelle version de l'application sur Cloud Run. Cela garantit que :

- ✅ La base de données est à jour avant le déploiement
- ✅ En cas d'échec des migrations, le déploiement est annulé
- ✅ Aucun risque d'incompatibilité entre le code et la base de données

## 🛠️ Exécution Manuelle des Migrations

Si vous devez exécuter les migrations manuellement (par exemple, pour un hotfix ou une maintenance), utilisez le script fourni :

### Vérifier les migrations en attente (Dry-run)

```bash
./scripts/run-migration-gcp.sh --dry-run
```

Cette commande affiche l'état des migrations sans les exécuter.

### Exécuter les migrations

```bash
./scripts/run-migration-gcp.sh
```

⚠️ **Attention** : Le script vous demandera confirmation avant d'exécuter les migrations en production.

### Variables d'environnement

Vous pouvez personnaliser la configuration avec ces variables :

```bash
export GCP_PROJECT_ID="votre-projet-id"
export GCP_REGION="europe-west9"
export CLOUD_SQL_INSTANCE="veloflott-db"
export IMAGE_TAG="latest"  # ou un tag spécifique

./scripts/run-migration-gcp.sh
```

## 🚨 Rollback des Migrations

Laravel ne supporte pas nativement le rollback automatique en production. Si vous devez annuler une migration :

### Option 1 : Rollback via Cloud Build

```bash
gcloud builds submit --no-source --config - <<EOF
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - 'gcr.io/\$PROJECT_ID/veloflott-api:latest'
      - '-s'
      - 'project-08eb5a0c-d370-4877-a5a:europe-west9:veloflott-db'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate:rollback'
      - '--step=1'
      - '--force'
EOF
```

### Option 2 : Rollback via Cloud SQL Proxy (local)

```bash
# 1. Démarrer le proxy Cloud SQL
cloud_sql_proxy -instances=PROJECT_ID:REGION:INSTANCE_NAME=tcp:5432

# 2. Configurer les variables d'environnement
export DB_HOST=127.0.0.1
export DB_PORT=5432

# 3. Exécuter le rollback
php artisan migrate:rollback --step=1
```

## 📊 Monitoring des Migrations

### Vérifier l'état des migrations

```bash
# Via le script (dry-run)
./scripts/run-migration-gcp.sh --dry-run

# Ou directement via gcloud
gcloud builds submit --no-source --config - <<EOF
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - 'gcr.io/\$PROJECT_ID/veloflott-api:latest'
      - '-s'
      - 'CLOUD_SQL_CONNECTION_NAME'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate:status'
EOF
```

### Logs des migrations

Les logs des migrations sont disponibles dans :
- **Cloud Build** : Console GCP > Cloud Build > History
- **Cloud Logging** : Filtrer par `resource.type="cloud_run_revision"`

```bash
# Voir les logs de la dernière build
gcloud builds log $(gcloud builds list --limit=1 --format='value(id)')
```

## 🔐 Sécurité

### Connexion à la base de données

Les migrations utilisent **Cloud SQL Proxy** via `exec-wrapper`, ce qui garantit :
- ✅ Connexion sécurisée via IAM
- ✅ Pas de stockage de credentials
- ✅ Connexion chiffrée SSL/TLS
- ✅ Isolation réseau

### Permissions requises

L'utilisateur/service account Cloud Build doit avoir les permissions :
- `cloudsql.instances.connect` - Pour se connecter à Cloud SQL
- `cloudsql.instances.get` - Pour récupérer les infos de l'instance

## 🧪 Testing des Migrations

Avant de déployer en production, testez vos migrations :

### 1. En local avec Docker

```bash
docker-compose up -d postgres
php artisan migrate:fresh --seed
```

### 2. Dans un environnement de staging

```bash
# Déployer sur un environnement de test
gcloud builds submit --config cloudbuild.yaml \
  --substitutions=_REGION=europe-west9,_CLOUD_SQL_CONNECTION_NAME=staging-instance
```

## 📝 Bonnes Pratiques

1. ✅ **Toujours tester** les migrations en local et staging avant production
2. ✅ **Écrire des migrations réversibles** quand possible (avec `down()`)
3. ✅ **Ne jamais supprimer de colonnes** contenant des données importantes sans backup
4. ✅ **Utiliser des transactions** pour les migrations critiques
5. ✅ **Documenter** les migrations complexes avec des commentaires
6. ⚠️ **Éviter** les migrations lourdes pendant les heures de pointe
7. ⚠️ **Attention** aux migrations qui ajoutent des contraintes sur de grandes tables

## 🆘 Dépannage

### Migration bloquée

Si une migration reste bloquée :

```bash
# 1. Vérifier les locks PostgreSQL
SELECT * FROM pg_locks WHERE NOT granted;

# 2. Terminer les connexions bloquantes
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = 'veloflott_db' AND state = 'idle in transaction';
```

### Échec de connexion à Cloud SQL

```bash
# Vérifier que l'instance est active
gcloud sql instances describe veloflott-db --project=PROJECT_ID

# Vérifier les permissions
gcloud projects get-iam-policy PROJECT_ID \
  --flatten="bindings[].members" \
  --filter="bindings.members:serviceAccount:PROJECT_ID@cloudbuild.gserviceaccount.com"
```

## 📚 Ressources

- [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
- [Cloud SQL Proxy](https://cloud.google.com/sql/docs/postgres/sql-proxy)
- [Cloud Build Documentation](https://cloud.google.com/build/docs)
- [GCP exec-wrapper](https://cloud.google.com/appengine/docs/flexible/reference/exec-wrapper)

---

**Dernière mise à jour** : 2026-02-06
**Système de tarification 3D** : 6 nouvelles migrations ajoutées (pricing_classes, duration_definitions, pricing_rates, discount_rules, rental_pricing_snapshots, bikes alteration)
