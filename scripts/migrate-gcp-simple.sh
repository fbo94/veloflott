#!/bin/bash
# Script one-liner pour exécuter les migrations sur GCP
# À exécuter dans Cloud Shell (https://console.cloud.google.com)

set -e

echo "🚀 Migration VeloFlott sur GCP"
echo ""

# Configuration automatique
PROJECT_ID=$(gcloud config get-value project 2>/dev/null)
if [ -z "$PROJECT_ID" ]; then
    echo "❌ Aucun projet GCP configuré"
    echo "Exécutez: gcloud config set project VOTRE_PROJECT_ID"
    exit 1
fi

echo "📦 Projet: $PROJECT_ID"

# Trouver l'instance Cloud SQL
INSTANCES=$(gcloud sql instances list --format="value(name)" 2>/dev/null)
if [ -z "$INSTANCES" ]; then
    echo "❌ Aucune instance Cloud SQL trouvée"
    exit 1
fi

# Prendre la première instance ou demander
INSTANCE_COUNT=$(echo "$INSTANCES" | wc -l | tr -d ' ')
if [ "$INSTANCE_COUNT" -eq 1 ]; then
    INSTANCE_NAME=$(echo "$INSTANCES" | head -1)
    echo "📊 Instance: $INSTANCE_NAME"
else
    echo "📊 Instances disponibles:"
    echo "$INSTANCES" | nl
    read -p "Choisissez le numéro de l'instance: " CHOICE
    INSTANCE_NAME=$(echo "$INSTANCES" | sed -n "${CHOICE}p")
fi

# Vérifier l'image Docker
IMAGE="gcr.io/$PROJECT_ID/veloflott-api:latest"
echo "🐳 Vérification de l'image: $IMAGE"

if ! gcloud container images describe "$IMAGE" &>/dev/null; then
    echo "⚠️  Image Docker non trouvée"
    echo ""
    echo "Option 1: Déclencher un build automatique"
    echo "  git push origin develop"
    echo ""
    echo "Option 2: Builder manuellement"
    echo "  gcloud builds submit --tag $IMAGE ."
    echo ""
    read -p "Voulez-vous continuer quand même? (y/N): " CONTINUE
    if [ "$CONTINUE" != "y" ] && [ "$CONTINUE" != "Y" ]; then
        exit 1
    fi
fi

# Obtenir la connexion Cloud SQL
CONNECTION_NAME=$(gcloud sql instances describe "$INSTANCE_NAME" --format="value(connectionName)")
echo "🔗 Connexion: $CONNECTION_NAME"

# Exécuter les migrations
echo ""
echo "⏳ Exécution des migrations..."
echo ""

gcloud builds submit --no-source --config=- <<YAML
steps:
  - name: 'gcr.io/google-appengine/exec-wrapper'
    args:
      - '-i'
      - '$IMAGE'
      - '-s'
      - '$CONNECTION_NAME'
      - '--'
      - 'php'
      - 'artisan'
      - 'migrate'
      - '--force'
      - '--no-interaction'
      - '-v'
timeout: '600s'
YAML

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migrations exécutées avec succès!"
    echo ""
    echo "🔍 Pour vérifier:"
    echo "  gcloud sql connect $INSTANCE_NAME --user=veloflott_user --database=postgres"
    echo "  SELECT migration FROM migrations ORDER BY batch DESC LIMIT 10;"
    echo ""
    echo "📊 Pour seed les données du pricing:"
    echo "  gcloud builds submit --no-source --config=- <<YAML"
    echo "steps:"
    echo "  - name: 'gcr.io/google-appengine/exec-wrapper'"
    echo "    args: ['-i', '$IMAGE', '-s', '$CONNECTION_NAME', '--', 'php', 'artisan', 'db:seed', '--class=PricingSystemSeeder', '--force']"
    echo "YAML"
else
    echo ""
    echo "❌ Erreur lors de l'exécution des migrations"
    echo "Consultez les logs: gcloud builds list --limit=1"
    exit 1
fi
