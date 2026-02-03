# Déploiement Keycloak - Quick Start

Ce dossier contient les fichiers pour déployer Keycloak en production sur GCP.

## 🚀 Déploiement Rapide (Cloud Run)

### Méthode 1 : Script Automatique (RECOMMANDÉ)

```bash
# 1. Définir votre projet GCP
export GCP_PROJECT_ID=votre-project-id

# 2. Lancer le script de déploiement
cd keycloak-deployment
./deploy-keycloak.sh
```

Le script va :
- ✅ Créer Cloud SQL instance (PostgreSQL)
- ✅ Configurer la base de données
- ✅ Stocker les secrets dans Secret Manager
- ✅ Déployer Keycloak sur Cloud Run
- ✅ Afficher l'URL et les credentials

**Temps estimé** : 10-15 minutes

---

### Méthode 2 : Commandes Manuelles

Voir le guide complet dans `../DEPLOYMENT-KEYCLOAK-GCP.md`

---

## 📋 Après le Déploiement

### 1. Accéder à Keycloak

```bash
# Récupérer l'URL
gcloud run services describe keycloak \
  --region europe-west1 \
  --format="value(status.url)"

# Ouvrir dans le navigateur
# https://keycloak-xxxxx.run.app
```

### 2. Configuration Initiale

1. **Se connecter**
   - Username: `admin`
   - Password: (défini lors du déploiement)

2. **Créer le Realm**
   - Master realm → Create Realm
   - Name: `veloflott`
   - Enabled: ON
   - Save

3. **Créer le Client**
   - Clients → Create client
   - Client ID: `veloflott-api`
   - Client authentication: ON
   - Save

   **Valid redirect URIs** :
   ```
   https://veloflott-api-xxxxx.run.app/*
   http://localhost/*
   ```

   **Web origins** :
   ```
   +
   ```

4. **Récupérer le Client Secret**
   - Onglet "Credentials"
   - Copier le "Client secret"
   - Stocker dans Secret Manager :

   ```bash
   echo -n "VOTRE_CLIENT_SECRET" | \
     gcloud secrets create veloflott-keycloak-secret \
     --data-file=- \
     --replication-policy="automatic"
   ```

5. **Récupérer la Clé Publique**

   ```bash
   curl https://keycloak-xxxxx.run.app/realms/veloflott/protocol/openid-connect/certs | jq .
   ```

### 3. Mettre à Jour l'Application Veloflott

```bash
# Mettre à jour les variables d'environnement
gcloud run services update veloflott-api \
  --region europe-west1 \
  --set-env-vars "KEYCLOAK_URL=https://keycloak-xxxxx.run.app,KEYCLOAK_PUBLIC_URL=https://keycloak-xxxxx.run.app,KEYCLOAK_REALM=veloflott,KEYCLOAK_CLIENT_ID=veloflott-api"

# Ajouter le secret
gcloud secrets add-iam-policy-binding veloflott-keycloak-secret \
  --member="serviceAccount:PROJECT_NUMBER-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"

# Attacher le secret au service
gcloud run services update veloflott-api \
  --region europe-west1 \
  --update-secrets "KEYCLOAK_CLIENT_SECRET=veloflott-keycloak-secret:latest"
```

---

## 🧪 Tester la Configuration

```bash
# 1. Tester que Keycloak répond
curl https://keycloak-xxxxx.run.app/realms/veloflott

# 2. Tester l'endpoint JWKS
curl https://keycloak-xxxxx.run.app/realms/veloflott/protocol/openid-connect/certs

# 3. Tester l'authentification de l'API
curl https://veloflott-api-xxxxx.run.app/api/health
```

---

## 🔧 Commandes Utiles

### Logs Keycloak

```bash
gcloud run services logs read keycloak \
  --region europe-west1 \
  --limit 50 \
  --follow
```

### Status du Service

```bash
gcloud run services describe keycloak \
  --region europe-west1
```

### Mettre à Jour Keycloak

```bash
gcloud run services update keycloak \
  --region europe-west1 \
  --image quay.io/keycloak/keycloak:26.0.0
```

### Récupérer les Secrets

```bash
# Admin password
gcloud secrets versions access latest \
  --secret=keycloak-admin-password

# DB password
gcloud secrets versions access latest \
  --secret=keycloak-db-password

# Client secret
gcloud secrets versions access latest \
  --secret=veloflott-keycloak-secret
```

---

## 💰 Coûts Estimés

**Configuration actuelle** (min-instances=1) :
- Cloud Run (Keycloak) : ~10-15€/mois
- Cloud SQL (db-f1-micro) : ~15-20€/mois
- **Total : ~25-35€/mois**

**Optimisation** (min-instances=0) :
```bash
gcloud run services update keycloak \
  --region europe-west1 \
  --min-instances 0

# Coût réduit à ~20-28€/mois
```

---

## 🆘 Troubleshooting

### Keycloak ne démarre pas

```bash
# Voir les logs d'erreur
gcloud run services logs read keycloak \
  --region europe-west1 \
  --limit 100

# Vérifier les variables d'environnement
gcloud run services describe keycloak \
  --region europe-west1 \
  --format="yaml(spec.template.spec.containers[0].env)"
```

### Erreur de connexion à la DB

```bash
# Vérifier que Cloud SQL est attaché
gcloud run services describe keycloak \
  --region europe-west1 \
  --format="value(spec.template.spec.containers[0].env)"

# Vérifier que l'instance Cloud SQL existe
gcloud sql instances list
```

### Problèmes d'authentification

1. Vérifier que le realm `veloflott` existe
2. Vérifier les Valid redirect URIs du client
3. Vérifier que le client secret est correct
4. Tester avec `curl` :

```bash
# Obtenir un token
curl -X POST \
  'https://keycloak-xxxxx.run.app/realms/veloflott/protocol/openid-connect/token' \
  -H 'Content-Type: application/x-www-form-urlencoded' \
  -d 'client_id=veloflott-api' \
  -d 'client_secret=VOTRE_SECRET' \
  -d 'grant_type=client_credentials'
```

---

## 📚 Documentation

- **Guide Complet** : `../DEPLOYMENT-KEYCLOAK-GCP.md`
- **Keycloak Docs** : https://www.keycloak.org/documentation
- **Cloud Run Docs** : https://cloud.google.com/run/docs

---

**🎉 Keycloak est prêt en production ! 🎉**
