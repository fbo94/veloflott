# Guide de Déploiement sur Google Cloud Platform (GCP)

Ce guide vous accompagne étape par étape pour déployer l'application Veloflott sur GCP tout en gardant votre environnement de développement local fonctionnel.

## 📋 Prérequis

- Compte GCP actif avec facturation activée
- `gcloud` CLI installé et configuré
- Docker installé localement
- Accès au projet GCP

## 🏗️ Architecture de Déploiement

```
┌─────────────────┐
│   Cloud Run     │ ← Application Laravel
│  (veloflott-api)│
└────────┬────────┘
         │
         ├──────→ ┌──────────────┐
         │        │  Cloud SQL   │ ← PostgreSQL
         │        │ (PostgreSQL) │
         │        └──────────────┘
         │
         ├──────→ ┌──────────────┐
         │        │Secret Manager│ ← Secrets
         │        └──────────────┘
         │
         └──────→ ┌──────────────┐
                  │Cloud Storage │ ← Fichiers
                  └──────────────┘
```

## 📝 Étape 1 : Configuration du Projet GCP

### 1.1 Créer ou sélectionner un projet

```bash
# Créer un nouveau projet
gcloud projects create veloflott-prod --name="Veloflott Production"

# Sélectionner le projet
gcloud config set project veloflott-prod

# Activer la facturation (via console web obligatoire)
# https://console.cloud.google.com/billing
```

### 1.2 Activer les APIs nécessaires

```bash
gcloud services enable \
  cloudbuild.googleapis.com \
  run.googleapis.com \
  sql-component.googleapis.com \
  sqladmin.googleapis.com \
  secretmanager.googleapis.com \
  storage-api.googleapis.com \
  storage-component.googleapis.com \
  containerregistry.googleapis.com
```

## 🗄️ Étape 2 : Configurer Cloud SQL (PostgreSQL)

### 2.1 Créer l'instance Cloud SQL

```bash
gcloud sql instances create veloflott-db \
  --database-version=POSTGRES_15 \
  --tier=db-f1-micro \
  --region=europe-west1 \
  --storage-type=SSD \
  --storage-size=10GB \
  --backup \
  --backup-start-time=03:00 \
  --maintenance-window-day=SUN \
  --maintenance-window-hour=04
```

### 2.2 Créer l'utilisateur et la base de données

```bash
# Définir le mot de passe root
gcloud sql users set-password postgres \
  --instance=veloflott-db \
  --password=VOTRE_MOT_DE_PASSE_SECURISE

# Créer la base de données
gcloud sql databases create veloflott_db \
  --instance=veloflott-db

# Créer l'utilisateur applicatif
gcloud sql users create veloflott_user \
  --instance=veloflott-db \
  --password=VOTRE_MOT_DE_PASSE_USER_SECURISE
```

### 2.3 Noter le nom de connexion

```bash
gcloud sql instances describe veloflott-db \
  --format="value(connectionName)"

# Format: PROJECT_ID:REGION:INSTANCE_NAME
# Exemple: veloflott-prod:europe-west1:veloflott-db
```

## 🔐 Étape 3 : Configurer Secret Manager

### 3.1 Créer les secrets

```bash
# APP_KEY
php artisan key:generate --show
# Copier la clé générée et créer le secret
echo -n "base64:VOTRE_CLE_GENEREE" | gcloud secrets create veloflott-app-key \
  --data-file=- \
  --replication-policy="automatic"

# DB_PASSWORD
echo -n "VOTRE_MOT_DE_PASSE_USER_SECURISE" | gcloud secrets create veloflott-db-password \
  --data-file=- \
  --replication-policy="automatic"

# KEYCLOAK_CLIENT_SECRET
echo -n "VOTRE_KEYCLOAK_SECRET" | gcloud secrets create veloflott-keycloak-secret \
  --data-file=- \
  --replication-policy="automatic"
```

### 3.2 Donner accès à Cloud Run

```bash
PROJECT_NUMBER=$(gcloud projects describe veloflott-prod --format="value(projectNumber)")

gcloud secrets add-iam-policy-binding veloflott-app-key \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"

gcloud secrets add-iam-policy-binding veloflott-db-password \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"

gcloud secrets add-iam-policy-binding veloflott-keycloak-secret \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

## 📦 Étape 4 : Configurer Cloud Storage (Optionnel)

```bash
# Créer le bucket pour les fichiers uploadés
gsutil mb -l europe-west1 gs://veloflott-storage

# Rendre le bucket accessible publiquement (si nécessaire)
gsutil iam ch allUsers:objectViewer gs://veloflott-storage
```

## 🚀 Étape 5 : Premier Déploiement Manuel

### 5.1 Build l'image Docker localement (test)

```bash
docker build -f Dockerfile.production -t veloflott-api:test .
```

### 5.2 Build et push vers GCR

```bash
# Configuration Docker pour GCR
gcloud auth configure-docker

# Build et tag
docker build -f Dockerfile.production \
  -t gcr.io/veloflott-prod/veloflott-api:v1.0.0 .

# Push vers GCR
docker push gcr.io/veloflott-prod/veloflott-api:v1.0.0
```

### 5.3 Déployer sur Cloud Run

```bash
gcloud run deploy veloflott-api \
  --image gcr.io/veloflott-prod/veloflott-api:v1.0.0 \
  --platform managed \
  --region europe-west1 \
  --allow-unauthenticated \
  --add-cloudsql-instances veloflott-prod:europe-west1:veloflott-db \
  --set-env-vars "APP_ENV=production,APP_DEBUG=false,LOG_CHANNEL=stderr,DB_CONNECTION=pgsql,DB_HOST=/cloudsql/veloflott-prod:europe-west1:veloflott-db,DB_PORT=5432,DB_DATABASE=veloflott_db,DB_USERNAME=veloflott_user" \
  --set-secrets "APP_KEY=veloflott-app-key:latest,DB_PASSWORD=veloflott-db-password:latest,KEYCLOAK_CLIENT_SECRET=velofloak-secret:latest" \
  --min-instances 1 \
  --max-instances 10 \
  --memory 512Mi \
  --cpu 1 \
  --timeout 300
```

## 🔄 Étape 6 : Exécuter les Migrations

### 6.1 Via Cloud Run Jobs (Recommandé)

```bash
# Créer un job pour les migrations
gcloud run jobs create veloflott-migrate \
  --image gcr.io/veloflott-prod/veloflott-api:v1.0.0 \
  --region europe-west1 \
  --add-cloudsql-instances veloflott-prod:europe-west1:veloflott-db \
  --set-env-vars "APP_ENV=production,DB_CONNECTION=pgsql,DB_HOST=/cloudsql/veloflott-prod:europe-west1:veloflott-db,DB_PORT=5432,DB_DATABASE=veloflott_db,DB_USERNAME=veloflott_user" \
  --set-secrets "APP_KEY=veloflott-app-key:latest,DB_PASSWORD=veloflott-db-password:latest" \
  --command php \
  --args artisan,migrate,--force

# Exécuter les migrations
gcloud run jobs execute veloflott-migrate --region europe-west1
```

### 6.2 Via Cloud SQL Proxy (Alternative locale)

```bash
# Télécharger Cloud SQL Proxy
curl -o cloud-sql-proxy https://storage.googleapis.com/cloud-sql-connectors/cloud-sql-proxy/v2.8.2/cloud-sql-proxy.darwin.amd64
chmod +x cloud-sql-proxy

# Lancer le proxy
./cloud-sql-proxy veloflott-prod:europe-west1:veloflott-db

# Dans un autre terminal, exécuter les migrations
DB_HOST=127.0.0.1 DB_PASSWORD=VOTRE_PASSWORD php artisan migrate --force
```

## 🔄 Étape 7 : CI/CD avec Cloud Build

### 7.1 Connecter votre repository GitHub

```bash
# Via la console web
# https://console.cloud.google.com/cloud-build/triggers

# Ou via gcloud
gcloud beta builds triggers create github \
  --repo-name=veloflott \
  --repo-owner=VOTRE_USERNAME \
  --branch-pattern="^main$" \
  --build-config=cloudbuild.yaml
```

### 7.2 Variables de substitution

Dans la console Cloud Build, configurer les substitutions :
- `_REGION`: `europe-west1`
- `_CLOUD_SQL_CONNECTION_NAME`: `veloflott-prod:europe-west1:veloflott-db`

## 🌐 Étape 8 : Configurer le Domaine Personnalisé (Optionnel)

```bash
# Mapper un domaine personnalisé
gcloud run domain-mappings create \
  --service veloflott-api \
  --domain api.veloflott.com \
  --region europe-west1

# Suivre les instructions pour configurer les DNS
```

## 🔍 Étape 9 : Monitoring et Logs

### 9.1 Voir les logs

```bash
# Logs en temps réel
gcloud run services logs read veloflott-api \
  --region europe-west1 \
  --limit 100 \
  --follow

# Logs via Console
# https://console.cloud.google.com/logs
```

### 9.2 Configurer les alertes (Optionnel)

Via la console Cloud Monitoring :
- Créer des alertes sur les erreurs HTTP 5xx
- Alertes sur l'utilisation mémoire > 80%
- Alertes sur les temps de réponse > 2s

## 🧪 Étape 10 : Tester le Déploiement

```bash
# Récupérer l'URL du service
SERVICE_URL=$(gcloud run services describe veloflott-api \
  --region europe-west1 \
  --format="value(status.url)")

# Tester le health check
curl $SERVICE_URL/health

# Tester l'API
curl $SERVICE_URL/api/documentation
```

## 🔒 Étape 11 : Sécurité Post-Déploiement

### 11.1 Configurer Cloud Armor (Optionnel)

Pour protection DDoS et WAF :
```bash
# Créer une politique de sécurité
gcloud compute security-policies create veloflott-policy \
  --description "Security policy for Veloflott API"

# Ajouter des règles (rate limiting, geo-blocking, etc.)
```

### 11.2 Activer Cloud IAP (Identity-Aware Proxy)

Pour contrôler l'accès à l'application :
```bash
gcloud iap web enable --resource-type=app-engine
```

## 📊 Étape 12 : Optimisations de Coût

### 12.1 Configurer l'autoscaling agressif

```bash
gcloud run services update veloflott-api \
  --region europe-west1 \
  --min-instances 0 \
  --max-instances 5 \
  --concurrency 80
```

### 12.2 Scheduler pour arrêter l'instance la nuit (dev/staging)

```bash
# Créer un job Cloud Scheduler pour scale down
gcloud scheduler jobs create http veloflott-scale-down \
  --schedule="0 22 * * *" \
  --http-method=PATCH \
  --uri="https://run.googleapis.com/v1/projects/veloflott-prod/locations/europe-west1/services/veloflott-api" \
  --message-body='{"spec":{"template":{"metadata":{"annotations":{"autoscaling.knative.dev/minScale":"0"}}}}}'
```

## 🔄 Développement Local (Reste Inchangé)

Votre environnement local continue de fonctionner normalement :

```bash
# Développement local comme avant
docker-compose up -d

# Accès à l'application locale
http://localhost
```

## 📝 Commandes Utiles

### Mettre à jour le service

```bash
# Après un push Git sur main, le déploiement est automatique via Cloud Build
# Ou manuellement :
gcloud run services update veloflott-api \
  --image gcr.io/veloflott-prod/veloflott-api:latest \
  --region europe-west1
```

### Rollback vers une version précédente

```bash
# Lister les révisions
gcloud run revisions list --service veloflott-api --region europe-west1

# Rollback
gcloud run services update-traffic veloflott-api \
  --region europe-west1 \
  --to-revisions REVISION_NAME=100
```

### Voir les métriques

```bash
# Via gcloud
gcloud run services describe veloflott-api \
  --region europe-west1 \
  --format="yaml(status)"

# Via console
# https://console.cloud.google.com/run
```

## ⚠️ Points d'Attention

1. **Keycloak** : Vous devez déployer Keycloak séparément (GKE, Cloud Run, ou service externe)
2. **Migrations** : Ne pas exécuter automatiquement en production, faire manuellement
3. **Secrets** : Ne JAMAIS committer les secrets dans Git
4. **Coûts** : Surveiller la console de facturation régulièrement
5. **Backups** : Cloud SQL fait des backups automatiques, mais tester la restauration

## 🆘 Troubleshooting

### L'application ne démarre pas

```bash
# Voir les logs détaillés
gcloud run services logs read veloflott-api \
  --region europe-west1 \
  --limit 50

# Vérifier que tous les secrets sont accessibles
gcloud secrets versions access latest --secret="veloflott-app-key"
```

### Erreurs de connexion à la BDD

```bash
# Vérifier que Cloud SQL est bien attaché
gcloud run services describe veloflott-api \
  --region europe-west1 \
  --format="value(spec.template.spec.containers[0].env)"

# Tester la connexion via Cloud SQL Proxy localement
```

### Problèmes de performances

```bash
# Augmenter les ressources
gcloud run services update veloflott-api \
  --memory 1Gi \
  --cpu 2 \
  --region europe-west1
```

## 📞 Support

- Documentation GCP : https://cloud.google.com/run/docs
- Community : https://stackoverflow.com/questions/tagged/google-cloud-run
- Support GCP : Console GCP > Support

---

**Félicitations ! Votre application Veloflott est maintenant déployée sur GCP ! 🎉**
