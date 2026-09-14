# syntax=docker/dockerfile:1

# ---- Frontend assets (Vite/Tailwind) ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---- PHP dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# ---- Runtime image ----
FROM php:8.3-apache AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Apache must serve the Laravel public/ directory, not the app root — only
# the DocumentRoot line is rewritten (not a blanket /var/www/ replace) so the
# rest of the default vhost/apache2.conf is left intact. A dedicated,
# more-specific <Directory> block for public/ grants AllowOverride there
# without loosening the broader /var/www/ block apache2.conf already has.
RUN sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n\tOptions -Indexes +FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
