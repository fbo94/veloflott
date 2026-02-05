#!/bin/sh
set -e

# Create symlink for storage if it doesn't exist
if [ ! -L /var/www/veloflott/public/storage ]; then
    echo "Creating storage symlink..."
    rm -rf /var/www/veloflott/public/storage
    ln -s /var/www/veloflott/storage/app/public /var/www/veloflott/public/storage
    echo "Storage symlink created successfully"
fi

# Execute the original nginx entrypoint
exec /docker-entrypoint.sh "$@"
