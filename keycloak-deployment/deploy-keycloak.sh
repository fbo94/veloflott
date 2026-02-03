#!/bin/bash

# Script de déploiement rapide Keycloak sur Cloud Run
# Usage: ./deploy-keycloak.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ID=${GCP_PROJECT_ID:-""}
REGION=${GCP_REGION:-"europe-west1"}
KEYCLOAK_DB_INSTANCE="keycloak-db"
KEYCLOAK_SERVICE="keycloak"

function print_header() {
    echo -e "${GREEN}==================================================${NC}"
    echo -e "${GREEN}  $1${NC}"
    echo -e "${GREEN}==================================================${NC}"
}

function print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

function print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

function print_error() {
    echo -e "${RED}❌ $1${NC}"
}

function print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Vérifier que PROJECT_ID est défini
if [ -z "$PROJECT_ID" ]; then
    print_error "GCP_PROJECT_ID n'est pas défini"
    echo ""
    echo "Définissez-le avec:"
    echo "  export GCP_PROJECT_ID=votre-project-id"
    echo ""
    exit 1
fi

print_header "Déploiement Keycloak sur Cloud Run"
echo ""
print_info "Project ID: $PROJECT_ID"
print_info "Region: $REGION"
echo ""

# Demander confirmation
read -p "$(echo -e ${YELLOW}Continuer avec le déploiement ? [y/N]${NC} ) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_warning "Déploiement annulé"
    exit 0
fi

# Étape 1: Créer Cloud SQL instance
print_header "Étape 1/5: Création Cloud SQL instance"

if gcloud sql instances describe $KEYCLOAK_DB_INSTANCE --project=$PROJECT_ID &>/dev/null; then
    print_warning "Instance Cloud SQL $KEYCLOAK_DB_INSTANCE existe déjà"
else
    print_info "Création de l'instance Cloud SQL..."
    gcloud sql instances create $KEYCLOAK_DB_INSTANCE \
        --database-version=POSTGRES_15 \
        --tier=db-f1-micro \
        --region=$REGION \
        --storage-type=SSD \
        --storage-size=10GB \
        --backup \
        --backup-start-time=02:00 \
        --project=$PROJECT_ID

    print_success "Instance Cloud SQL créée"
fi

# Étape 2: Configurer la base de données
print_header "Étape 2/5: Configuration base de données"

print_info "Définir le mot de passe root PostgreSQL..."
read -sp "Entrez le mot de passe root PostgreSQL: " ROOT_PASSWORD
echo
gcloud sql users set-password postgres \
    --instance=$KEYCLOAK_DB_INSTANCE \
    --password=$ROOT_PASSWORD \
    --project=$PROJECT_ID

print_info "Création de la base de données keycloak..."
gcloud sql databases create keycloak \
    --instance=$KEYCLOAK_DB_INSTANCE \
    --project=$PROJECT_ID 2>/dev/null || print_warning "Base de données existe déjà"

print_info "Création de l'utilisateur keycloak_user..."
read -sp "Entrez le mot de passe pour keycloak_user: " DB_PASSWORD
echo
gcloud sql users create keycloak_user \
    --instance=$KEYCLOAK_DB_INSTANCE \
    --password=$DB_PASSWORD \
    --project=$PROJECT_ID 2>/dev/null || print_warning "Utilisateur existe déjà"

print_success "Base de données configurée"

# Étape 3: Créer les secrets
print_header "Étape 3/5: Création des secrets"

print_info "Création du secret admin password..."
read -sp "Entrez le mot de passe admin Keycloak: " ADMIN_PASSWORD
echo
echo -n "$ADMIN_PASSWORD" | gcloud secrets create keycloak-admin-password \
    --data-file=- \
    --replication-policy="automatic" \
    --project=$PROJECT_ID 2>/dev/null || \
    (echo -n "$ADMIN_PASSWORD" | gcloud secrets versions add keycloak-admin-password --data-file=- --project=$PROJECT_ID)

print_info "Création du secret DB password..."
echo -n "$DB_PASSWORD" | gcloud secrets create keycloak-db-password \
    --data-file=- \
    --replication-policy="automatic" \
    --project=$PROJECT_ID 2>/dev/null || \
    (echo -n "$DB_PASSWORD" | gcloud secrets versions add keycloak-db-password --data-file=- --project=$PROJECT_ID)

# Donner accès à Cloud Run
PROJECT_NUMBER=$(gcloud projects describe $PROJECT_ID --format="value(projectNumber)")

gcloud secrets add-iam-policy-binding keycloak-admin-password \
    --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
    --role="roles/secretmanager.secretAccessor" \
    --project=$PROJECT_ID >/dev/null

gcloud secrets add-iam-policy-binding keycloak-db-password \
    --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
    --role="roles/secretmanager.secretAccessor" \
    --project=$PROJECT_ID >/dev/null

print_success "Secrets créés et permissions configurées"

# Étape 4: Déployer Keycloak sur Cloud Run
print_header "Étape 4/5: Déploiement Keycloak sur Cloud Run"

CLOUD_SQL_CONNECTION="${PROJECT_ID}:${REGION}:${KEYCLOAK_DB_INSTANCE}"

gcloud run deploy $KEYCLOAK_SERVICE \
    --image quay.io/keycloak/keycloak:25.0.0 \
    --platform managed \
    --region $REGION \
    --allow-unauthenticated \
    --add-cloudsql-instances $CLOUD_SQL_CONNECTION \
    --set-env-vars "KC_DB=postgres,KC_DB_URL_HOST=/cloudsql/${CLOUD_SQL_CONNECTION},KC_DB_URL_DATABASE=keycloak,KC_DB_USERNAME=keycloak_user,KC_HOSTNAME_STRICT=false,KC_PROXY=edge,KC_HTTP_ENABLED=true,KEYCLOAK_ADMIN=admin,KC_HEALTH_ENABLED=true" \
    --set-secrets "KEYCLOAK_ADMIN_PASSWORD=keycloak-admin-password:latest,KC_DB_PASSWORD=keycloak-db-password:latest" \
    --args start \
    --min-instances 1 \
    --max-instances 3 \
    --memory 1Gi \
    --cpu 1 \
    --timeout 300 \
    --project=$PROJECT_ID

print_success "Keycloak déployé sur Cloud Run"

# Étape 5: Afficher les informations
print_header "Étape 5/5: Informations de déploiement"

SERVICE_URL=$(gcloud run services describe $KEYCLOAK_SERVICE \
    --region $REGION \
    --format="value(status.url)" \
    --project=$PROJECT_ID)

echo ""
print_success "Déploiement terminé !"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${BLUE}📋 Informations de connexion:${NC}"
echo ""
echo -e "  🌐 URL Keycloak:     ${GREEN}${SERVICE_URL}${NC}"
echo -e "  👤 Username:         ${GREEN}admin${NC}"
echo -e "  🔑 Password:         ${GREEN}(stocké dans Secret Manager)${NC}"
echo ""
echo -e "${BLUE}📝 Prochaines étapes:${NC}"
echo ""
echo "  1. Accéder à Keycloak: $SERVICE_URL"
echo "  2. Se connecter avec admin / [password]"
echo "  3. Créer un realm 'veloflott'"
echo "  4. Créer un client 'veloflott-api'"
echo "  5. Récupérer le client secret"
echo ""
echo -e "${BLUE}🔧 Commandes utiles:${NC}"
echo ""
echo "  # Voir les logs"
echo "  gcloud run services logs read keycloak --region $REGION --project $PROJECT_ID"
echo ""
echo "  # Mettre à jour le service"
echo "  gcloud run services update keycloak --region $REGION --project $PROJECT_ID"
echo ""
echo "  # Récupérer le secret admin"
echo "  gcloud secrets versions access latest --secret=keycloak-admin-password --project $PROJECT_ID"
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
