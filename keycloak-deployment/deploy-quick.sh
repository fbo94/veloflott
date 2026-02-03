#!/bin/bash

# Déploiement rapide Keycloak sur Cloud Run
# Ce script copie l'image depuis quay.io vers GCR puis déploie

set -e

PROJECT_ID="project-08eb5a0c-d370-4877-a5a"
REGION="europe-west1"
KEYCLOAK_VERSION="25.0.0"

echo "🚀 Déploiement Keycloak sur Cloud Run"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Étape 1: Pull l'image depuis quay.io
echo "📥 1/4 - Pull de l'image Keycloak depuis quay.io..."
docker pull quay.io/keycloak/keycloak:${KEYCLOAK_VERSION}

# Étape 2: Tag pour GCR
echo "🏷️  2/4 - Tag de l'image pour GCR..."
docker tag quay.io/keycloak/keycloak:${KEYCLOAK_VERSION} \
  gcr.io/${PROJECT_ID}/keycloak:${KEYCLOAK_VERSION}

docker tag quay.io/keycloak/keycloak:${KEYCLOAK_VERSION} \
  gcr.io/${PROJECT_ID}/keycloak:latest

# Étape 3: Configure Docker et push
echo "☁️  3/4 - Push de l'image vers GCR..."
gcloud auth configure-docker --quiet
docker push gcr.io/${PROJECT_ID}/keycloak:${KEYCLOAK_VERSION}
docker push gcr.io/${PROJECT_ID}/keycloak:latest

# Étape 4: Déployer sur Cloud Run
echo "🚀 4/4 - Déploiement sur Cloud Run..."
gcloud run deploy keycloak \
  --image gcr.io/${PROJECT_ID}/keycloak:${KEYCLOAK_VERSION} \
  --platform managed \
  --region ${REGION} \
  --allow-unauthenticated \
  --add-cloudsql-instances ${PROJECT_ID}:${REGION}:keycloak-db \
  --set-env-vars "KC_DB=postgres,KC_DB_URL_HOST=/cloudsql/${PROJECT_ID}:${REGION}:keycloak-db,KC_DB_URL_DATABASE=keycloak,KC_DB_USERNAME=keycloak_user,KC_HOSTNAME_STRICT=false,KC_PROXY=edge,KC_HTTP_ENABLED=true,KEYCLOAK_ADMIN=admin" \
  --set-secrets "KEYCLOAK_ADMIN_PASSWORD=keycloak-admin-password:latest,KC_DB_PASSWORD=keycloak-db-password:latest" \
  --args start \
  --min-instances 1 \
  --max-instances 3 \
  --memory 1Gi \
  --cpu 1 \
  --timeout 300 \
  --project ${PROJECT_ID}

echo ""
echo "✅ Déploiement terminé !"
echo ""

# Récupérer l'URL
SERVICE_URL=$(gcloud run services describe keycloak \
  --region ${REGION} \
  --format="value(status.url)" \
  --project ${PROJECT_ID})

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 URL Keycloak: ${SERVICE_URL}"
echo "👤 Username: admin"
echo "🔑 Password: (dans Secret Manager)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Récupérer le password admin:"
echo "   gcloud secrets versions access latest --secret=keycloak-admin-password"
echo ""
