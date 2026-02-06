FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install and enable Xdebug via PECL (clean, single-load)
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && { \
        echo "xdebug.start_with_request=yes"; \
        echo "xdebug.client_host=host.docker.internal"; \
        echo "xdebug.client_port=9003"; \
      } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copy custom PHP configuration for file uploads
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Le workdir correspond au volume mount dans docker-compose.yml
WORKDIR /var/www/veloflott

# Pas de COPY ici - le code est monté via volume dans docker-compose.yml
# Les répertoires storage seront créés au démarrage via volume mount

# Exposer le port FPM (Nginx y accède via réseau)
EXPOSE 9000

CMD ["php-fpm"]
