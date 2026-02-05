# Configuration du stockage des photos de vélos

## Vue d'ensemble

L'application stocke les photos des vélos de manière différente selon l'environnement:
- **Développement**: Stockage local dans `storage/app/public/bikes`
- **Production**: Google Cloud Storage (GCS)

## Configuration locale (développement)

### 1. Variables d'environnement

Dans votre fichier `.env`:

```env
FILESYSTEM_DISK=local
BIKE_PHOTOS_DRIVER=local
APP_URL=http://localhost
```

### 2. Créer le lien symbolique

Pour rendre les photos accessibles publiquement:

```bash
php artisan storage:link
```

Cela créera un lien symbolique de `public/storage` vers `storage/app/public`.

### 3. Permissions

Assurez-vous que le répertoire `storage/app/public/bikes` est accessible en écriture:

```bash
chmod -R 775 storage/app/public
```

## Configuration production (GCP)

### 1. Installer le driver Google Cloud Storage

```bash
composer require google/cloud-storage
composer require league/flysystem-google-cloud-storage
```

### 2. Créer un bucket GCS

1. Accédez à la console GCP: https://console.cloud.google.com
2. Créez un nouveau bucket ou utilisez un existant
3. Configurez les permissions publiques si nécessaire

### 3. Créer un compte de service

1. Dans GCP Console, allez dans "IAM & Admin" > "Service Accounts"
2. Créez un nouveau compte de service
3. Accordez les permissions suivantes:
   - `Storage Object Admin` (pour créer/supprimer des fichiers)
   - `Storage Object Viewer` (pour lire les fichiers)
4. Créez une clé JSON et téléchargez-la

### 4. Variables d'environnement production

Dans votre fichier `.env` de production:

```env
FILESYSTEM_DISK=bike_photos
BIKE_PHOTOS_DRIVER=gcs

# GCP Configuration
GCS_PROJECT_ID=votre-project-id
GCS_BUCKET=veloflott-bikes-photos
GCS_KEY_FILE=/path/to/service-account-key.json
GCS_PATH_PREFIX=
GCS_STORAGE_API_URI=https://storage.googleapis.com
```

### 5. Sécurité

- **NE JAMAIS** commiter le fichier de clé JSON dans Git
- Ajoutez le fichier au `.gitignore`
- En production, utilisez des secrets managers (Google Secret Manager, Kubernetes Secrets, etc.)

## Utilisation des endpoints

### Upload d'une photo

```bash
POST /api/fleet/bikes/{id}/photos
Content-Type: multipart/form-data
Authorization: Bearer {token}

Body:
- photo: [fichier image, max 5MB]
```

Réponse (201):
```json
{
  "bike_id": "uuid",
  "photo_url": "http://localhost/storage/bikes/{id}/{filename}"
}
```

### Suppression d'une photo

```bash
DELETE /api/fleet/bikes/{id}/photos
Content-Type: application/json
Authorization: Bearer {token}

Body:
{
  "photo_url": "http://localhost/storage/bikes/{id}/{filename}"
}
```

Réponse (204): No Content

## Structure des fichiers

Les photos sont organisées par vélo:

```
storage/app/public/bikes/
├── {bike-id-1}/
│   ├── {uuid-1}.jpg
│   ├── {uuid-2}.jpg
│   └── {uuid-3}.png
├── {bike-id-2}/
│   └── {uuid-4}.jpg
└── ...
```

## Formats supportés

- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)
- WebP (.webp)
- Taille maximale: 5MB par photo

## Migration des données

Si vous avez des photos existantes à migrer vers GCS:

```bash
# Script à créer pour migrer les photos locales vers GCS
php artisan bikes:migrate-photos-to-gcs
```

## Troubleshooting

### Erreur: "File not found"
- Vérifiez que le lien symbolique existe: `ls -la public/storage`
- Si nécessaire, recréez-le: `php artisan storage:link`

### Erreur: "Permission denied"
- Vérifiez les permissions: `chmod -R 775 storage`
- Vérifiez le propriétaire: `chown -R www-data:www-data storage`

### Erreur GCS: "Invalid credentials"
- Vérifiez que le chemin vers la clé JSON est correct
- Vérifiez que le compte de service a les bonnes permissions
- Vérifiez que le projet ID et le bucket sont corrects
