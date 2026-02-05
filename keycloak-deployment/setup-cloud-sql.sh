#!/bin/bash
# Configure Cloud SQL pour accepter les connexions depuis Cloud Run

set -e

PROJECT_ID="project-08eb5a0c-d370-4877-a5a"
REGION="europe-west1"
INSTANCE="keycloak-db"

echo "🔧 Configuration de Cloud SQL pour Cloud Run..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Vérifier l'instance
echo "📋 Vérification de l'instance ${INSTANCE}..."
gcloud sql instances describe ${INSTANCE} \
  --project ${PROJECT_ID} \
  --format="value(ipAddresses[0].ipAddress)" > /dev/null 2>&1

if [ $? -ne 0 ]; then
  echo "❌ L'instance ${INSTANCE} n'existe pas ou n'a pas d'IP publique"
  echo ""
  echo "Créez d'abord l'instance avec:"
  echo "  gcloud sql instances create ${INSTANCE} \\"
  echo "    --database-version=POSTGRES_15 \\"
  echo "    --tier=db-f1-micro \\"
  echo "    --region=${REGION} \\"
  echo "    --assign-ip \\"
  echo "    --backup \\"
  echo "    --project ${PROJECT_ID}"
  exit 1
fi

# Activer les connexions publiques et autoriser toutes les IPs (Cloud Run a des IPs dynamiques)
echo "🌐 Activation des connexions publiques..."
gcloud sql instances patch ${INSTANCE} \
  --assign-ip \
  --authorized-networks=0.0.0.0/0 \
  --database-flags=cloudsql.iam_authentication=off \
  --project ${PROJECT_ID} \
  --quiet

echo ""
echo "✅ Configuration terminée!"
echo ""
echo "⚠️  ATTENTION: L'instance accepte maintenant les connexions depuis n'importe quelle IP."
echo "   C'est acceptable pour un environnement de développement."
echo "   Pour la production, utilisez VPC Connector + Private IP."
echo ""
