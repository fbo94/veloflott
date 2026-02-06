#!/bin/bash
# Script pour exécuter les migrations manuellement sur GCP Cloud SQL
# Usage: ./scripts/run-migration-gcp.sh [--dry-run]

set -e

# Configuration
PROJECT_ID="${GCP_PROJECT_ID:-veloflott-prod}"
REGION="${GCP_REGION:-europe-west9}"
CLOUD_SQL_INSTANCE="${CLOUD_SQL_INSTANCE:-veloflott-db}"
IMAGE_TAG="${IMAGE_TAG:-latest}"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction de log
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifier les dépendances
if ! command -v gcloud &> /dev/null; then
    log_error "gcloud CLI n'est pas installé. Installez-le depuis: https://cloud.google.com/sdk/docs/install"
    exit 1
fi

# Vérifier l'authentification
if ! gcloud auth list --filter=status:ACTIVE --format="value(account)" | grep -q .; then
    log_error "Vous n'êtes pas authentifié. Exécutez: gcloud auth login"
    exit 1
fi

log_info "Projet GCP: $PROJECT_ID"
log_info "Région: $REGION"
log_info "Instance Cloud SQL: $CLOUD_SQL_INSTANCE"
log_info "Image Docker: gcr.io/$PROJECT_ID/veloflott-api:$IMAGE_TAG"

# Mode dry-run
DRY_RUN=false
if [[ "$1" == "--dry-run" ]]; then
    DRY_RUN=true
    log_warn "Mode DRY-RUN activé - les migrations ne seront PAS exécutées"
fi

# Confirmation
if [[ "$DRY_RUN" == "false" ]]; then
    echo ""
    read -p "⚠️  Êtes-vous sûr de vouloir exécuter les migrations en production ? (oui/non): " -r
    echo
    if [[ ! $REPLY =~ ^[Oo][Uu][Ii]$ ]]; then
        log_info "Migration annulée."
        exit 0
    fi
fi

# Construction de la commande
CLOUD_SQL_CONNECTION_NAME="$PROJECT_ID:$REGION:$CLOUD_SQL_INSTANCE"

log_info "Connexion à Cloud SQL: $CLOUD_SQL_CONNECTION_NAME"

if [[ "$DRY_RUN" == "true" ]]; then
    # Dry-run: afficher les migrations en attente
    log_info "Exécution de 'migrate:status' (dry-run)..."
    gcloud builds submit --no-source \
        --config /dev/stdin <<EOF
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - 'gcr.io/$PROJECT_ID/veloflott-api:$IMAGE_TAG'
      - '-s'
      - '$CLOUD_SQL_CONNECTION_NAME'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate:status'
EOF
else
    # Exécution réelle des migrations
    log_info "Exécution des migrations..."
    gcloud builds submit --no-source \
        --config /dev/stdin <<EOF
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - 'gcr.io/$PROJECT_ID/veloflott-api:$IMAGE_TAG'
      - '-s'
      - '$CLOUD_SQL_CONNECTION_NAME'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate'
      - '--force'
      - '--no-interaction'
EOF

    if [ $? -eq 0 ]; then
        log_info "✅ Migrations exécutées avec succès!"
    else
        log_error "❌ Échec des migrations"
        exit 1
    fi
fi

log_info "Terminé."
