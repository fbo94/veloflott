# Exécuter les migrations sur GCP

## 🎯 Méthode recommandée : Cloud Shell

### Étape 1 : Ouvrir Cloud Shell

1. Allez sur https://console.cloud.google.com
2. Sélectionnez votre projet **VeloFlott**
3. Cliquez sur l'icône **Cloud Shell** (>_) en haut à droite
4. Attendez que le terminal s'ouvre

### Étape 2 : Méthode simple (Cloud Build)

Dans Cloud Shell, exécutez ces commandes :

```bash
# 1. Variables d'environnement
PROJECT_ID=$(gcloud config get-value project)
INSTANCE_NAME="veloflott-db"  # Remplacez si différent
IMAGE="gcr.io/$PROJECT_ID/veloflott-api:latest"

# 2. Obtenir la connexion Cloud SQL
CONNECTION_NAME=$(gcloud sql instances describe $INSTANCE_NAME --format="value(connectionName)")

# 3. Exécuter les migrations via Cloud Build
cat << 'YAML' | envsubst | gcloud builds submit --no-source --config=-
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - '${IMAGE}'
      - '-s'
      - '${CONNECTION_NAME}'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate'
      - '--force'
      - '--no-interaction'
      - '-v'
timeout: '600s'
YAML
```

### Étape 3 : Vérifier que ça a marché

```bash
# Voir les logs du build
gcloud builds list --limit=1

# Ou se connecter directement à la DB
gcloud sql connect veloflott-db --user=veloflott_user --database=veloflott_db
```

Puis dans psql :
```sql
-- Voir les dernières migrations
SELECT migration, batch
FROM migrations
ORDER BY batch DESC
LIMIT 15;

-- Vérifier que les tables du pricing existent
\dt pricing*
\dt duration*
\dt discount*
```

Vous devriez voir :
- ✅ `pricing_classes`
- ✅ `duration_definitions`
- ✅ `pricing_rates`
- ✅ `discount_rules`
- ✅ `rental_pricing_snapshots`

---

## 🔧 Méthode alternative : Cloud SQL Proxy local

Si Cloud Shell ne fonctionne pas :

```bash
# 1. Télécharger Cloud SQL Proxy
curl -o cloud-sql-proxy https://storage.googleapis.com/cloud-sql-connectors/cloud-sql-proxy/v2.8.0/cloud-sql-proxy.darwin.arm64
chmod +x cloud-sql-proxy

# 2. Démarrer le proxy
./cloud-sql-proxy --port 5433 PROJECT_ID:REGION:INSTANCE_NAME &

# 3. Exécuter les migrations localement
DB_HOST=127.0.0.1 DB_PORT=5433 php artisan migrate --force
```

---

## 🚨 En cas de problème

### L'image Docker n'existe pas ?

```bash
# Construire l'image depuis votre code local
gcloud builds submit --tag gcr.io/$(gcloud config get-value project)/veloflott-api:latest .
```

### Erreur de permissions ?

```bash
# Vérifier vos permissions
gcloud projects get-iam-policy $(gcloud config get-value project) \
  --flatten="bindings[].members" \
  --filter="bindings.members:$(gcloud config get-value account)"
```

Vous devez avoir au minimum :
- `roles/cloudsql.client`
- `roles/cloudbuild.builds.editor`

### La base n'existe pas ?

```bash
# Lister les bases de données
gcloud sql databases list --instance=veloflott-db

# Créer la base si nécessaire
gcloud sql databases create veloflott_db --instance=veloflott-db
```

---

## ✅ Après les migrations

Une fois les migrations exécutées, vous pouvez aussi seed les données :

```bash
# Via Cloud Build
cat << 'YAML' | envsubst | gcloud builds submit --no-source --config=-
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - '${IMAGE}'
      - '-s'
      - '${CONNECTION_NAME}'
      - '--'
      - 'php'
      - 'artisan'
      - 'db:seed'
      - '--class=PricingSystemSeeder'
      - '--force'
timeout: '600s'
YAML
```

Cela créera :
- 3 classes tarifaires (Standard, Premium, Elite)
- 8 durées (demi-journée → mois)
- Grille tarifaire complète (catégorie × classe × durée)
- 3 règles de réduction (7j: -10%, 14j: -15%, 30j: -20%)
