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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/webservices

COPY .. /var/www/webservices

RUN mkdir "/var/www/webservices/storage" \
    && mkdir -p /var/www/webservices/storage/logs \
    && mkdir -p /var/www/webservices/storage/framework/sessions \
    && mkdir -p /var/www/webservices/storage/framework/views \
    && mkdir -p /var/www/webservices/storage/framework/cache

# Installer les dépendances Composer (lance ça manuellement après build si besoin)
# RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/webservices \
    && chmod -R 755 /var/www/webservices/storage

# Exposer le port FPM (Nginx y accède via réseau)
EXPOSE 9000

CMD ["php-fpm"]
