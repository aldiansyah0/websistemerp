FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
    nginx supervisor nodejs npm \
    mysql-client git curl zip unzip \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    oniguruma-dev icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd opcache intl

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/webstellarerp

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build && rm -rf node_modules

RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY deploy/nginx/default.conf /etc/nginx/http.d/default.conf
COPY deploy/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
COPY deploy/php/php.ini /usr/local/etc/php/conf.d/webstellar.ini
COPY deploy/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
CMD ["/entrypoint.sh"]
