# Déploiement Keycloak sur Google Cloud Platform

Ce guide présente **3 options** pour déployer Keycloak sur GCP, du plus simple au plus complexe.

## 📊 Comparaison des Options

| Option | Complexité | Coût/mois | Scalabilité | Maintenance | Recommandé pour |
|--------|-----------|-----------|-------------|-------------|-----------------|
| **Cloud Run** | ⭐ Facile | ~15-30€ | Auto | Minimale | **PME, Startups** ✅ |
| **GKE** | ⭐⭐⭐ Complexe | ~70-150€ | Excellente | Élevée | Grandes entreprises |
| **Compute Engine** | ⭐⭐ Moyen | ~30-50€ | Manuelle | Moyenne | Cas spécifiques |

**Recommandation** : **Option 1 - Cloud Run** (simple, économique, scalable)

---

# Option 1 : Cloud Run (RECOMMANDÉ) ⭐

## Avantages
- ✅ Setup en 15 minutes
- ✅ Auto-scaling (0 à N instances)
- ✅ Certificat SSL automatique
- ✅ Coût minimal (pay-per-use)
- ✅ Maintenance minimale
- ✅ Haute disponibilité

## Architecture

```
┌─────────────────────┐
│  Cloud Run          │
│  (Keycloak)         │ ← Port 8080 (HTTP)
└──────────┬──────────┘
           │
           ├──→ Cloud SQL PostgreSQL (keycloak_db)
           ├──→ Secret Manager (KEYCLOAK_ADMIN_PASSWORD)
           └──→ Cloud Load Balancer (HTTPS)
```

---

## 🚀 Déploiement Keycloak sur Cloud Run

### Étape 1 : Créer la base de données Keycloak

```bash
# Instance Cloud SQL pour Keycloak (avec IP publique)
gcloud sql instances create keycloak-db \
  --database-version=POSTGRES_15 \
  --tier=db-f1-micro \
  --region=europe-west1 \
  --storage-type=SSD \
  --storage-size=10GB \
  --backup \
  --backup-start-time=02:00 \
  --assign-ip

# Autoriser les connexions depuis Cloud Run (toutes les IPs)
gcloud sql instances patch keycloak-db \
  --authorized-networks=0.0.0.0/0 \
  --quiet

# Définir le mot de passe root
gcloud sql users set-password postgres \
  --instance=keycloak-db \
  --password=cHK82mFDvF0zUOHmQhfJ

# Créer la base de données Keycloak
gcloud sql databases create keycloak \
  --instance=keycloak-db

# Créer l'utilisateur Keycloak
gcloud sql users create keycloak_user \
  --instance=keycloak-db \
  --password=mqw8wyWppTcoZ6N24CdjLBX

```

### Étape 2 : Stocker les secrets

```bash
# Mot de passe admin Keycloak
echo -n "9DH5zy83OaMJSZZkiuzTt5d" | \
  gcloud secrets create keycloak-admin-password \
  --data-file=- \
  --replication-policy="automatic"

# Mot de passe DB Keycloak
echo -n "t8TOpWOrEaDaOYJrxdjx4kY" | \
  gcloud secrets create keycloak-db-password \
  --data-file=- \
  --replication-policy="automatic"

# Donner accès à Cloud Run
PROJECT_NUMBER=$(gcloud projects describe project-08eb5a0c-d370-4877-a5a --format="value(projectNumber)")

gcloud secrets add-iam-policy-binding keycloak-admin-password \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"

gcloud secrets add-iam-policy-binding keycloak-db-password \
  --member="serviceAccount:${PROJECT_NUMBER}-compute@developer.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

### Étape 3 : Déployer Keycloak sur Cloud Run

**IMPORTANT** : Utilisez le script automatisé qui construit l'image pour linux/amd64 et configure automatiquement la connexion à Cloud SQL :

```bash
cd keycloak-deployment
./build-and-deploy.sh
```

Le script va :
- ✅ Construire l'image optimisée pour linux/amd64 (compatible Cloud Run)
- ✅ Push vers GCR
- ✅ Récupérer l'IP de Cloud SQL automatiquement
- ✅ Déployer sur Cloud Run avec la configuration complète

**Note de sécurité** : Cette configuration utilise l'IP publique de Cloud SQL. Pour plus de sécurité en production, considérez :
- Utiliser VPC Connector + IP privée
- Restreindre les IPs autorisées dans Cloud SQL
- Activer Cloud SQL Auth Proxy

### Étape 4 : Configurer un domaine personnalisé (Optionnel)

```bash
# Mapper votre domaine
gcloud run domain-mappings create \
  --service keycloak \
  --domain auth.votre-domaine.com \
  --region europe-west1

# Suivre les instructions pour configurer les DNS (A et AAAA records)
```

### Étape 5 : Configuration initiale Keycloak

1. **Accéder à Keycloak**
   ```bash
   # Récupérer l'URL
   gcloud run services describe keycloak \
     --region europe-west1 \
     --format="value(status.url)"

   # Ouvrir dans le navigateur
   # https://keycloak-xxxxx.run.app
   ```

2. **Se connecter**
   - Username: `admin`
   - Password: Celui défini dans Secret Manager

3. **Créer le Realm `veloflott`**
   - Cliquer sur "Create Realm"
   - Name: `veloflott`
   - Enabled: ON

4. **Créer le Client `veloflott-api`**
   - Realm: `veloflott`
   - Clients → Create client
   - Client ID: `veloflott-api`
   - Client authentication: ON
   - Valid redirect URIs:
     - `https://veloflott-api-xxxxx.run.app/*`
     - `http://localhost/*` (pour dev)
   - Web origins: `+` (même que redirect URIs)

5. **Récupérer les credentials**
   - Onglet "Credentials"
   - Copier le "Client secret"
   - Mettre à jour `.env.production` avec ce secret

6. **Récupérer la clé publique**
   ```bash
   curl https://keycloak-xxxxx.run.app/realms/veloflott/protocol/openid-connect/certs
   ```

### Étape 6 : Mettre à jour l'application Veloflott

Mettre à jour les variables d'environnement de `veloflott-api` :

```bash
gcloud run services update veloflott-api \
  --region europe-west1 \
  --set-env-vars "KEYCLOAK_URL=https://keycloak-xxxxx.run.app,KEYCLOAK_PUBLIC_URL=https://keycloak-xxxxx.run.app,KEYCLOAK_REALM=veloflott,KEYCLOAK_CLIENT_ID=veloflott-api,KEYCLOAK_TLS_VERIFY=true" \
  --update-secrets "KEYCLOAK_CLIENT_SECRET=veloflott-keycloak-secret:latest"
```

---

## 💰 Coûts Estimés Cloud Run

**Configuration Minimale** (1 instance min) :
- Cloud Run Keycloak : ~10-15€/mois
- Cloud SQL (db-f1-micro) : ~15-20€/mois
- **Total : ~25-35€/mois**

**Optimisation** (min-instances=0) :
- Cloud Run : ~5-8€/mois (pay-per-use)
- Cloud SQL : ~15-20€/mois
- **Total : ~20-28€/mois**

---

# Option 2 : Google Kubernetes Engine (GKE)

## Pour qui ?
- Grandes entreprises
- Besoin de contrôle fin
- Multi-régions / HA avancée
- Déjà une expertise Kubernetes

## Déploiement Rapide

### 1. Créer un cluster GKE

```bash
gcloud container clusters create keycloak-cluster \
  --zone europe-west1-b \
  --num-nodes 2 \
  --machine-type e2-medium \
  --enable-autoscaling \
  --min-nodes 1 \
  --max-nodes 3
```

### 2. Déployer avec Helm

```bash
# Ajouter le repo Bitnami
helm repo add bitnami https://charts.bitnami.com/bitnami
helm repo update

# Installer Keycloak
helm install keycloak bitnami/keycloak \
  --set auth.adminUser=admin \
  --set auth.adminPassword=VOTRE_PASSWORD \
  --set postgresql.auth.password=VOTRE_DB_PASSWORD \
  --set postgresql.persistence.enabled=true \
  --set postgresql.persistence.size=10Gi \
  --set service.type=LoadBalancer \
  --set ingress.enabled=true \
  --set ingress.hostname=auth.votre-domaine.com
```

### 3. Récupérer l'IP externe

```bash
kubectl get svc keycloak -o jsonpath='{.status.loadBalancer.ingress[0].ip}'
```

## 💰 Coûts GKE

- GKE Cluster : ~50-70€/mois (2 nodes e2-medium)
- Load Balancer : ~20€/mois
- Persistent Disks : ~5€/mois
- **Total : ~75-100€/mois**

---

# Option 3 : Compute Engine (VM)

## Pour qui ?
- Besoin de contrôle total
- Migration depuis on-premise
- Cas d'usage spécifiques

## Déploiement

### 1. Créer une VM

```bash
gcloud compute instances create keycloak-vm \
  --zone=europe-west1-b \
  --machine-type=e2-medium \
  --image-family=ubuntu-2204-lts \
  --image-project=ubuntu-os-cloud \
  --boot-disk-size=20GB \
  --tags=http-server,https-server
```

### 2. Se connecter et installer

```bash
# SSH dans la VM
gcloud compute ssh keycloak-vm --zone=europe-west1-b

# Installer Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Installer Docker Compose
sudo apt install docker-compose -y

# Créer docker-compose.yml pour Keycloak
cat > docker-compose.yml <<EOF
version: '3.8'
services:
  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: keycloak
      POSTGRES_USER: keycloak
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data

  keycloak:
    image: quay.io/keycloak/keycloak:25.0.0
    command: start
    environment:
      KC_DB: postgres
      KC_DB_URL_HOST: postgres
      KC_DB_USERNAME: keycloak
      KC_DB_PASSWORD: ${DB_PASSWORD}
      KEYCLOAK_ADMIN: admin
      KEYCLOAK_ADMIN_PASSWORD: ${ADMIN_PASSWORD}
      KC_HOSTNAME_STRICT: false
      KC_PROXY: edge
    ports:
      - "8080:8080"
    depends_on:
      - postgres

volumes:
  postgres_data:
EOF

# Démarrer
sudo docker-compose up -d
```

### 3. Configurer le firewall

```bash
gcloud compute firewall-rules create allow-keycloak \
  --allow tcp:8080 \
  --source-ranges 0.0.0.0/0 \
  --target-tags http-server
```

## 💰 Coûts Compute Engine

- VM e2-medium : ~30€/mois
- Persistent Disk : ~5€/mois
- Static IP : ~5€/mois
- **Total : ~40€/mois**

---

# Option 4 : Services Managés Externes

## Auth0 (Okta)
- **Avantages** : Zéro maintenance, UI moderne, documentation excellente
- **Coûts** : Gratuit jusqu'à 7000 MAU, puis ~25€/mois
- **Migration** : Remplacer Keycloak par Auth0 SDK

## Firebase Authentication
- **Avantages** : Intégré GCP, gratuit jusqu'à 10k MAU
- **Coûts** : Pay-as-you-go au-delà
- **Migration** : Adapter le middleware Laravel

## Okta Workforce Identity
- **Avantages** : Enterprise-grade, compliance
- **Coûts** : À partir de ~50€/mois
- **Pour** : Grandes entreprises

---

# 🎯 Recommandation Finale

## Pour Veloflott (PME/Startup) : **Cloud Run** ✅

**Pourquoi ?**
1. ✅ **Simple** : Déploiement en 15 minutes
2. ✅ **Économique** : ~25-35€/mois
3. ✅ **Scalable** : Auto-scaling automatique
4. ✅ **Maintenance** : Quasi-nulle
5. ✅ **Cohérent** : Même stack que l'API (Cloud Run + Cloud SQL)

**Prochaines étapes** :
1. Suivre les étapes 1-6 de l'Option 1
2. Tester l'authentification
3. Configurer le monitoring (Cloud Logging)

---

# 📋 Checklist de Déploiement

### Avant de déployer
- [ ] Choisir l'option (Cloud Run recommandé)
- [ ] Créer Cloud SQL pour Keycloak
- [ ] Stocker les secrets dans Secret Manager
- [ ] Réserver un domaine (optionnel mais recommandé)

### Déploiement
- [ ] Déployer Keycloak sur Cloud Run
- [ ] Configurer le realm `veloflott`
- [ ] Créer le client `veloflott-api`
- [ ] Récupérer le client secret
- [ ] Tester la connexion

### Post-déploiement
- [ ] Mettre à jour les variables de `veloflott-api`
- [ ] Tester l'authentification end-to-end
- [ ] Configurer les backups Cloud SQL
- [ ] Activer les logs Cloud Logging
- [ ] Documenter les credentials

---

# 🆘 Troubleshooting

### Keycloak ne démarre pas

```bash
# Voir les logs
gcloud run services logs read keycloak --region europe-west1

# Vérifier les secrets
gcloud secrets versions access latest --secret=keycloak-admin-password
```

### Erreur de connexion à la DB

```bash
# Vérifier que Cloud SQL est attaché
gcloud run services describe keycloak \
  --region europe-west1 \
  --format="value(spec.template.metadata.annotations)"
```

### CORS / Redirect errors

Vérifier dans Keycloak Admin :
- Clients → veloflott-api → Valid redirect URIs
- Clients → veloflott-api → Web origins

---

# 📞 Support

- **Keycloak Docs** : https://www.keycloak.org/documentation
- **Cloud Run Docs** : https://cloud.google.com/run/docs
- **Community** : https://github.com/keycloak/keycloak/discussions

---

**🎉 Votre Keycloak sera prêt en production en 15 minutes avec Cloud Run ! 🎉**
