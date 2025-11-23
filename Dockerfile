# --- STAGE 1 : Vendor PHP ---
FROM composer:latest AS vendor_builder
WORKDIR /app
COPY . .
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --prefer-dist

# --- STAGE 2 : Build Assets & Node Modules ---
FROM node:22-alpine AS node_builder
WORKDIR /app
COPY . .
COPY --from=vendor_builder /app/vendor ./vendor
RUN npm ci
RUN npm run build
# NOTE : Pas de 'npm prune' pour garder les libs nécessaires au SSR

# --- STAGE 3 : Production ---
FROM php:8.3-fpm

# Install système + Redis + Node + Autoconf
RUN apt-get update && apt-get install -y \
    nginx \
    supervisor \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    nodejs \
    npm \
    autoconf \
    && apt-get clean

# Extension Redis (Vital pour les sessions/cache)
RUN pecl install redis && docker-php-ext-enable redis

# Extensions PHP standards
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Fichiers projet
COPY . .
COPY --from=node_builder /app/public/build public/build
COPY --from=node_builder /app/bootstrap/ssr bootstrap/ssr
COPY --from=vendor_builder /app/vendor vendor
COPY --from=node_builder /app/node_modules node_modules

# Configs (On suppose qu'elles sont dans un dossier 'docker/' à la racine)
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

# Permissions & Logs
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN mkdir -p /var/www/storage/logs && chown -R www-data:www-data /var/www/storage/logs

EXPOSE 80
CMD ["/usr/bin/supervisord"]