# Note: this Dockerfile is full of bad practices

FROM node:22 AS node_builder

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build


FROM serversideup/php:8.4-fpm-nginx

USER root

RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    mbstring \
    gd \
    exif

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY --from=node_builder /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data storage bootstrap/cache

RUN php artisan config:clear

USER www-data