# Déploiement Keycloak sur Cloud Run - Guide Rapide

## Prérequis

1. Instance Cloud SQL PostgreSQL existante
2. Secrets créés dans Secret Manager
3. gcloud configuré avec le bon projet

## Étapes de déploiement

### 1. Configurer Cloud SQL

```bash
chmod +x setup-cloud-sql.sh
./setup-cloud-sql.sh
```

Cette commande va :
- ✅ Activer l'IP publique sur Cloud SQL
- ✅ Autoriser les connexions depuis Cloud Run (0.0.0.0/0)
- ✅ Désactiver IAM authentication (utilise password à la place)

### 2. Vérifier les secrets

```bash
# Vérifier que les secrets existent
gcloud secrets describe keycloak-admin-password --project=project-08eb5a0c-d370-4877-a5a
gcloud secrets describe keycloak-db-password --project=project-08eb5a0c-d370-4877-a5a
```

Si les secrets n'existent pas, créez-les :

```bash
# Admin password
echo -n "VOTRE_MOT_DE_PASSE_ADMIN" | \
  gcloud secrets create keycloak-admin-password \
  --data-file=- \
  --replication-policy="automatic" \
  --project=project-08eb5a0c-d370-4877-a5a

# DB password
echo -n "VOTRE_MOT_DE_PASSE_DB" | \
  gcloud secrets create keycloak-db-password \
  --data-file=- \
  --replication-policy="automatic" \
  --project=project-08eb5a0c-d370-4877-a5a
```

### 3. Déployer Keycloak

```bash
chmod +x build-and-deploy.sh
./build-and-deploy.sh
```

Le script va :
1. Builder l'image pour linux/amd64
2. Push vers GCR
3. Récupérer l'IP de Cloud SQL
4. Déployer sur Cloud Run

## Troubleshooting

### Erreur: "Failed to obtain JDBC connection"

**Cause**: Cloud SQL refuse les connexions

**Solution**:
```bash
# Vérifier que l'IP publique est activée
gcloud sql instances describe keycloak-db \
  --project=project-08eb5a0c-d370-4877-a5a \
  --format="value(ipAddresses[0].ipAddress)"

# Relancer la configuration
./setup-cloud-sql.sh
```

### Erreur: "Acquisition timeout"

**Cause**: Mauvais mot de passe ou base de données inexistante

**Solution**:
```bash
# Vérifier que la base existe
gcloud sql databases list --instance=keycloak-db --project=project-08eb5a0c-d370-4877-a5a

# Vérifier le mot de passe
gcloud secrets versions access latest --secret=keycloak-db-password --project=project-08eb5a0c-d370-4877-a5a
```

### Voir les logs en temps réel

```bash
gcloud run services logs read keycloak \
  --region=europe-west1 \
  --project=project-08eb5a0c-d370-4877-a5a \
  --follow
```

## Sécurité

⚠️ **IMPORTANT**: Cette configuration utilise une IP publique et autorise toutes les connexions (0.0.0.0/0).

**Pour la production**, utilisez :
- VPC Connector + IP privée
- Cloud SQL Auth Proxy
- Restriction des IPs autorisées

## Accéder à Keycloak

Une fois déployé, récupérez l'URL :

```bash
gcloud run services describe keycloak \
  --region=europe-west1 \
  --project=project-08eb5a0c-d370-4877-a5a \
  --format="value(status.url)"
```

Connectez-vous avec :
- Username: `admin`
- Password: (dans Secret Manager)

```bash
gcloud secrets versions access latest --secret=keycloak-admin-password
```
