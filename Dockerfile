FROM serversideup/php:8.4-fpm-nginx

USER root

RUN install-php-extensions pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data . /var/www/html

WORKDIR /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan config:cache && php artisan route:cache

USER www-data