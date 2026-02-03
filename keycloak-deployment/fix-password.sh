#!/bin/bash
# Synchronise les mots de passe entre Secret Manager et Cloud SQL

set -e

PROJECT_ID="project-08eb5a0c-d370-4877-a5a"
INSTANCE="keycloak-db"

echo "🔧 Synchronisation des mots de passe..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Récupérer le mot de passe depuis Secret Manager
echo "📋 Récupération du mot de passe depuis Secret Manager..."
DB_PASSWORD=$(gcloud secrets versions access latest \
  --secret=keycloak-db-password \
  --project=${PROJECT_ID})

if [ -z "$DB_PASSWORD" ]; then
  echo "❌ Impossible de récupérer le mot de passe depuis Secret Manager"
  exit 1
fi

echo "✅ Mot de passe récupéré (${#DB_PASSWORD} caractères)"
echo ""

# Mettre à jour le mot de passe de l'utilisateur keycloak_user dans Cloud SQL
echo "🔐 Mise à jour du mot de passe dans Cloud SQL..."
gcloud sql users set-password keycloak_user \
  --instance=${INSTANCE} \
  --password="${DB_PASSWORD}" \
  --project=${PROJECT_ID}

echo ""
echo "✅ Mot de passe synchronisé avec succès!"
echo ""
echo "Vous pouvez maintenant redéployer Keycloak:"
echo "  ./build-and-deploy.sh"
echo ""
