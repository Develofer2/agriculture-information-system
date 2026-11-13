FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev ca-certificates \
    && docker-php-ext-install pdo_pgsql \
    && a2enmod headers \
    && rm -rf /var/lib/apt/lists/*

COPY public/ /var/www/html/
COPY config/ /var/www/config/
COPY certs/ /var/www/certs/
COPY database/ /var/www/database/
COPY scripts/ /var/www/scripts/

RUN mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www/storage

WORKDIR /var/www

EXPOSE 80
