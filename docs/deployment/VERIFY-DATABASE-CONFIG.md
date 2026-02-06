# Vérification de la configuration de base de données

Ce document explique comment s'assurer que l'application utilise la bonne base de données (`postgres`) sur GCP.

## ✅ Checklist complète

### 1. Vérifier Cloud Run

```bash
# Voir toutes les variables d'environnement
gcloud run services describe veloflott-api \
  --region=europe-west9 \
  --format="yaml(spec.template.spec.containers[0].env)"
```

**Vérifiez que** :
```yaml
- name: DB_DATABASE
  value: postgres  # ✅ Doit être "postgres"
```

**Si incorrect, corriger** :
```bash
gcloud run services update veloflott-api \
  --region=europe-west9 \
  --update-env-vars=DB_DATABASE=postgres
```

### 2. Vérifier le .env local

```bash
grep DB_DATABASE .env
```

**Résultat attendu** :
```
DB_DATABASE=postgres
```

**Si incorrect** :
```bash
sed -i 's/DB_DATABASE=.*/DB_DATABASE=postgres/' .env
```

### 3. Vérifier cloudbuild.yaml

```bash
grep -A5 "run-migrations" cloudbuild.yaml | grep DB_DATABASE
```

**Résultat attendu** :
```yaml
env:
  - 'DB_DATABASE=postgres'
```

### 4. Vérifier dans la base de données

```bash
# Se connecter
gcloud sql connect $(gcloud sql instances list --format="value(name)" --limit=1) \
  --user=veloflott_user \
  --database=postgres
```

Puis vérifier les tables :
```sql
-- Lister toutes les tables
\dt

-- Vérifier les migrations
SELECT migration FROM migrations ORDER BY id DESC LIMIT 10;

-- Compter les tables du pricing
SELECT COUNT(*)
FROM pg_tables
WHERE schemaname = 'public'
  AND (tablename LIKE '%pricing%'
    OR tablename LIKE '%duration%'
    OR tablename LIKE '%discount%');
-- Devrait retourner 5 (pricing_classes, pricing_rates, duration_definitions, discount_rules, rental_pricing_snapshots)
```

### 5. Tester l'application en production

```bash
# Récupérer l'URL de l'app
APP_URL=$(gcloud run services describe veloflott-api \
  --region=europe-west9 \
  --format="value(status.url)")

# Tester un endpoint (avec authentification si nécessaire)
curl -X GET "$APP_URL/api/v1/health"
```

### 6. Vérifier les logs en temps réel

```bash
# Logs Cloud Run
gcloud run services logs read veloflott-api \
  --region=europe-west9 \
  --limit=50

# Chercher les erreurs de connexion DB
gcloud run services logs read veloflott-api \
  --region=europe-west9 \
  --limit=100 | grep -i "database\|connection\|postgres"
```

### 7. Vérifier les secrets (si utilisés)

```bash
# Lister les secrets
gcloud secrets list

# Voir la config d'un secret
gcloud secrets describe db-password

# Vérifier les versions
gcloud secrets versions list db-password
```

## 🔧 Commandes de correction rapide

### Tout corriger en une fois

```bash
# 1. Corriger .env local
sed -i 's/DB_DATABASE=.*/DB_DATABASE=postgres/' .env

# 2. Corriger Cloud Run
gcloud run services update veloflott-api \
  --region=europe-west9 \
  --update-env-vars=DB_DATABASE=postgres

# 3. Vérifier
echo "=== .env local ==="
grep DB_DATABASE .env

echo "=== Cloud Run ==="
gcloud run services describe veloflott-api \
  --region=europe-west9 \
  --format="value(spec.template.spec.containers[0].env)" | grep DB_DATABASE
```

## 🚨 Dépannage

### L'app utilise toujours veloflott_db

Si après correction l'app utilise toujours l'ancienne base :

1. **Redémarrer Cloud Run** :
```bash
# Forcer un redémarrage en changeant une annotation
gcloud run services update veloflott-api \
  --region=europe-west9 \
  --update-annotations=restart-timestamp=$(date +%s)
```

2. **Vérifier le cache** :
```bash
# Se connecter au container en cours
gcloud run services proxy veloflott-api --region=europe-west9
```

3. **Redéployer complètement** :
```bash
git push origin develop
```

### Vérifier quelle base est utilisée en temps réel

Ajouter un endpoint de debug temporaire qui affiche :
```php
// routes/api.php
Route::get('/debug/db', function() {
    return [
        'connection' => config('database.default'),
        'database' => config('database.connections.pgsql.database'),
        'host' => config('database.connections.pgsql.host'),
        'current_db' => DB::select('SELECT current_database()')[0]->current_database,
    ];
});
```

Puis tester :
```bash
curl -X GET "$APP_URL/api/v1/debug/db"
```

## ✅ Validation finale

Tous ces éléments doivent pointer vers `postgres` :
- ✅ `.env` local → `DB_DATABASE=postgres`
- ✅ `cloudbuild.yaml` → `env: ['DB_DATABASE=postgres']`
- ✅ Cloud Run env vars → `DB_DATABASE=postgres`
- ✅ Tables existent dans la base `postgres`
- ✅ Migrations enregistrées dans `postgres.migrations`
- ✅ App fonctionne sans erreur de connexion
