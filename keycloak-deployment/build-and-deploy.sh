#!/bin/bash

# Build et déploiement Keycloak optimisé pour Cloud Run
set -e

PROJECT_ID="project-08eb5a0c-d370-4877-a5a"
REGION="europe-west1"

echo "🏗️  Building optimized Keycloak image for Cloud Run..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Étape 1: Build l'image optimisée pour linux/amd64 (Cloud Run)
echo "🏗️  1/3 - Building optimized Keycloak image for linux/amd64..."
cd "$(dirname "$0")"
docker build --platform linux/amd64 -t gcr.io/${PROJECT_ID}/keycloak:optimized .

# Étape 2: Push vers GCR
echo "☁️  2/3 - Pushing to GCR..."
gcloud auth configure-docker --quiet
docker push gcr.io/${PROJECT_ID}/keycloak:optimized

# Obtenir l'IP publique de Cloud SQL
echo "🔍 Getting Cloud SQL instance IP..."
DB_HOST=$(gcloud sql instances describe keycloak-db \
  --project ${PROJECT_ID} \
  --format="value(ipAddresses[0].ipAddress)")

echo "📡 Database host: ${DB_HOST}"

# Étape 3: Déployer sur Cloud Run
echo "🚀 3/3 - Deploying to Cloud Run..."
gcloud run deploy keycloak \
  --image gcr.io/${PROJECT_ID}/keycloak:optimized \
  --platform managed \
  --region ${REGION} \
  --allow-unauthenticated \
  --set-env-vars "KC_DB=postgres,KC_DB_URL_HOST=${DB_HOST},KC_DB_URL_DATABASE=keycloak,KC_DB_USERNAME=keycloak_user,KC_HOSTNAME_STRICT=false,KC_PROXY=edge,KC_HTTP_ENABLED=true,KC_HEALTH_ENABLED=true,KEYCLOAK_ADMIN=admin" \
  --set-secrets "KEYCLOAK_ADMIN_PASSWORD=keycloak-admin-password:latest,KC_DB_PASSWORD=keycloak-db-password:latest" \
  --min-instances 1 \
  --max-instances 3 \
  --memory 1Gi \
  --cpu 1 \
  --timeout 600 \
  --port 8080 \
  --project ${PROJECT_ID}

echo ""
echo "✅ Deployment completed!"
echo ""

# Récupérer l'URL
SERVICE_URL=$(gcloud run services describe keycloak \
  --region ${REGION} \
  --format="value(status.url)" \
  --project ${PROJECT_ID})

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 Keycloak URL: ${SERVICE_URL}"
echo "👤 Username: admin"
echo "🔑 Password: (in Secret Manager)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Get admin password:"
echo "   gcloud secrets versions access latest --secret=keycloak-admin-password"
echo ""
