#!/bin/bash
# Création des secrets pour Cloud Run

set -e

PROJECT_ID="project-08eb5a0c-d370-4877-a5a"

echo "🔐 Création des secrets pour VeloFlott API"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. APP_KEY - Générer une clé Laravel
echo "📝 1/3 - Génération de APP_KEY..."
APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")

if gcloud secrets describe veloflott-app-key --project=$PROJECT_ID &>/dev/null; then
    echo "   Secret veloflott-app-key existe déjà, mise à jour..."
    echo -n "$APP_KEY" | gcloud secrets versions add veloflott-app-key --data-file=- --project=$PROJECT_ID
else
    echo "   Création du secret veloflott-app-key..."
    echo -n "$APP_KEY" | gcloud secrets create veloflott-app-key \
        --data-file=- \
        --replication-policy="automatic" \
        --project=$PROJECT_ID
fi
echo "   ✅ APP_KEY configuré"

# 2. DB_PASSWORD - Mot de passe Cloud SQL
echo ""
echo "📝 2/3 - Configuration de DB_PASSWORD..."
read -sp "   Entrez le mot de passe de la base de données Cloud SQL: " DB_PASSWORD
echo ""

if gcloud secrets describe veloflott-db-password --project=$PROJECT_ID &>/dev/null; then
    echo "   Secret veloflott-db-password existe déjà, mise à jour..."
    echo -n "$DB_PASSWORD" | gcloud secrets versions add veloflott-db-password --data-file=- --project=$PROJECT_ID
else
    echo "   Création du secret veloflott-db-password..."
    echo -n "$DB_PASSWORD" | gcloud secrets create veloflott-db-password \
        --data-file=- \
        --replication-policy="automatic" \
        --project=$PROJECT_ID
fi
echo "   ✅ DB_PASSWORD configuré"

# 3. KEYCLOAK_CLIENT_SECRET - Secret Keycloak
echo ""
echo "📝 3/3 - Configuration de KEYCLOAK_CLIENT_SECRET..."
read -sp "   Entrez le client secret Keycloak: " KEYCLOAK_SECRET
echo ""

if gcloud secrets describe veloflott-keycloak-secret --project=$PROJECT_ID &>/dev/null; then
    echo "   Secret veloflott-keycloak-secret existe déjà, mise à jour..."
    echo -n "$KEYCLOAK_SECRET" | gcloud secrets versions add veloflott-keycloak-secret --data-file=- --project=$PROJECT_ID
else
    echo "   Création du secret veloflott-keycloak-secret..."
    echo -n "$KEYCLOAK_SECRET" | gcloud secrets create veloflott-keycloak-secret \
        --data-file=- \
        --replication-policy="automatic" \
        --project=$PROJECT_ID
fi
echo "   ✅ KEYCLOAK_CLIENT_SECRET configuré"

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Tous les secrets ont été créés avec succès!"
echo ""
echo "Vous pouvez maintenant redéployer:"
echo "  gcloud builds submit --config=cloudbuild.yaml --project=$PROJECT_ID"
echo ""
