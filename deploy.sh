#!/bin/bash

# Script helper pour le déploiement Veloflott
# Usage: ./deploy.sh [command]

set -e

PROJECT_ID=${GCP_PROJECT_ID:-"veloflott-prod"}
REGION=${GCP_REGION:-"europe-west1"}
SERVICE_NAME="veloflott-api"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

function print_header() {
    echo -e "${GREEN}==================================================${NC}"
    echo -e "${GREEN}  $1${NC}"
    echo -e "${GREEN}==================================================${NC}"
}

function print_info() {
    echo -e "${YELLOW}ℹ️  $1${NC}"
}

function print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

function print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Commandes disponibles

function local_up() {
    print_header "Démarrage environnement LOCAL"
    docker-compose up -d
    print_success "Environnement local démarré"
    echo ""
    echo "URLs locales:"
    echo "  - Application: http://localhost"
    echo "  - API Docs: http://localhost/api/documentation"
    echo "  - Keycloak: https://keycloak.localhost:8443"
}

function local_down() {
    print_header "Arrêt environnement LOCAL"
    docker-compose down
    print_success "Environnement local arrêté"
}

function local_logs() {
    docker-compose logs -f php
}

function local_shell() {
    docker-compose exec php bash
}

function local_migrate() {
    print_header "Exécution migrations LOCAL"
    docker-compose exec php php artisan migrate
    print_success "Migrations exécutées"
}

function local_test() {
    print_header "Exécution tests LOCAL"
    docker-compose exec php php artisan test
}

function build_production() {
    print_header "Build image PRODUCTION"
    docker build -f Dockerfile.production -t gcr.io/${PROJECT_ID}/${SERVICE_NAME}:latest .
    print_success "Image buildée: gcr.io/${PROJECT_ID}/${SERVICE_NAME}:latest"
}

function push_production() {
    print_header "Push image vers GCR"
    gcloud auth configure-docker --quiet
    docker push gcr.io/${PROJECT_ID}/${SERVICE_NAME}:latest
    print_success "Image pushée vers GCR"
}

function deploy_production() {
    print_header "Déploiement sur Cloud Run PRODUCTION"

    print_info "Vérification du projet GCP..."
    gcloud config set project ${PROJECT_ID}

    print_info "Déploiement du service..."
    gcloud run deploy ${SERVICE_NAME} \
        --image gcr.io/${PROJECT_ID}/${SERVICE_NAME}:latest \
        --platform managed \
        --region ${REGION} \
        --allow-unauthenticated

    print_success "Déploiement terminé"

    SERVICE_URL=$(gcloud run services describe ${SERVICE_NAME} \
        --region ${REGION} \
        --format="value(status.url)")

    echo ""
    echo "Service URL: ${SERVICE_URL}"
    echo "Health check: ${SERVICE_URL}/api/health"
}

function production_logs() {
    print_header "Logs PRODUCTION"
    gcloud run services logs read ${SERVICE_NAME} \
        --region ${REGION} \
        --limit 100 \
        --follow
}

function production_status() {
    print_header "Status PRODUCTION"
    gcloud run services describe ${SERVICE_NAME} \
        --region ${REGION} \
        --format="table(status.url,status.conditions[0].type,spec.template.spec.containers[0].image)"
}

function production_migrate() {
    print_header "Exécution migrations PRODUCTION"

    print_info "Démarrage d'un job de migration..."
    gcloud run jobs execute veloflott-migrate --region ${REGION}

    print_success "Migrations exécutées"
}

function full_deploy() {
    print_header "Déploiement COMPLET (Build + Push + Deploy)"
    build_production
    push_production
    deploy_production
}

function test_production() {
    print_header "Test PRODUCTION"

    SERVICE_URL=$(gcloud run services describe ${SERVICE_NAME} \
        --region ${REGION} \
        --format="value(status.url)")

    print_info "Testing health endpoint..."
    curl -s ${SERVICE_URL}/api/health | jq .

    print_info "Testing API documentation..."
    curl -I ${SERVICE_URL}/api/documentation

    print_success "Tests terminés"
}

function show_help() {
    cat << EOF
Usage: ./deploy.sh [command]

📦 COMMANDES LOCALES (Développement):
  local:up          Démarrer l'environnement local (docker-compose up)
  local:down        Arrêter l'environnement local
  local:logs        Voir les logs en temps réel
  local:shell       Ouvrir un shell dans le container PHP
  local:migrate     Exécuter les migrations localement
  local:test        Exécuter les tests

🚀 COMMANDES PRODUCTION (GCP):
  build             Build l'image Docker de production
  push              Push l'image vers Google Container Registry
  deploy            Déployer sur Cloud Run
  full              Build + Push + Deploy (tout en un)

  prod:logs         Voir les logs de production
  prod:status       Voir le status du service
  prod:migrate      Exécuter les migrations en production
  prod:test         Tester les endpoints de production

📖 AIDE:
  help              Afficher cette aide

VARIABLES D'ENVIRONNEMENT:
  GCP_PROJECT_ID    ID du projet GCP (défaut: veloflott-prod)
  GCP_REGION        Région GCP (défaut: europe-west1)

EXEMPLES:
  ./deploy.sh local:up           # Démarrer en local
  ./deploy.sh full               # Déployer en production
  ./deploy.sh prod:logs          # Voir les logs de prod

EOF
}

# Router les commandes
case "$1" in
    # Local
    local:up)       local_up ;;
    local:down)     local_down ;;
    local:logs)     local_logs ;;
    local:shell)    local_shell ;;
    local:migrate)  local_migrate ;;
    local:test)     local_test ;;

    # Production
    build)          build_production ;;
    push)           push_production ;;
    deploy)         deploy_production ;;
    full)           full_deploy ;;

    prod:logs)      production_logs ;;
    prod:status)    production_status ;;
    prod:migrate)   production_migrate ;;
    prod:test)      test_production ;;

    # Help
    help|"")        show_help ;;

    *)
        print_error "Commande inconnue: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
