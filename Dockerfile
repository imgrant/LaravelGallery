FROM composer:1.7 as vendor

COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock

RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist


FROM node:8.11 as frontend

RUN mkdir -p /app/public

COPY package.json webpack.mix.js /app/
COPY resources/assets/ /app/resources/assets/
COPY public/ /app/public/

WORKDIR /app

RUN npm install && npm run production


FROM php:7.1-fpm as app_server

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Copy sample php.ini file into place
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/php.file-upload-limits.ini "$PHP_INI_DIR/conf.d/file-upload-limits.ini"

USER www-data

# Copy app & resources from earlier stages
COPY --chown=www-data . /var/www
COPY --chown=www-data --from=vendor /app/vendor/ /var/www/vendor/
COPY --chown=www-data --from=frontend /app/public/ /var/www/public/

# Set working directory
WORKDIR /var/www

RUN php artisan route:cache && \
    php artisan view:cache


FROM nginx:1.17-alpine as web_server

COPY docker/nginx/gallery.conf /etc/nginx/conf.d/default.conf

COPY --from=frontend /app/public /var/www/public
