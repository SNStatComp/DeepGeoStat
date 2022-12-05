ARG NODE_VERSION=lts
ARG COMPOSER_VERSION=latest
ARG PHP_VERSION=fpm

# Build Vue app
FROM node:${NODE_VERSION} AS node
COPY ./ /var/www/html/
WORKDIR /var/www/html/

RUN npm install \
 && npm run production

# Get php packages
FROM composer:${COMPOSER_VERSION} AS composer
COPY ./ /var/www/html/
WORKDIR /var/www/html/

RUN composer install \
    --optimize-autoloader \
    --no-dev

# Build Docker image for production
FROM php:${PHP_VERSION}
WORKDIR /var/www/html

RUN apt-get update \
 && apt-get dist-upgrade -y \
 && apt-get install -y \
    libzip-dev \
    zip \
    libpq-dev \
    gdal-bin \
    postgresql-client \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data ./ /var/www/html/
COPY --from=node --chown=www-data:www-data /var/www/html/public/ /var/www/html/public/
COPY --from=composer --chown=www-data:www-data /var/www/html/vendor/ /var/www/html/vendor/

## php extensions
# phpredis
RUN curl -L $(curl -s https://api.github.com/repos/phpredis/phpredis/releases/latest | grep "tarball_url" | cut -d '"' -f 4) --output phpredis.tar.gz \
 && tar -xvf phpredis.tar.gz --one-top-level --strip-components=1 \
 && cd phpredis \
 && phpize \
 && ./configure \
 && make \
 && make install \
 && docker-php-ext-enable redis.so \
 && cd ../ \
 && rm -r phpredis \
          phpredis.tar.gz

RUN docker-php-ext-install \
    pgsql \
    pdo_pgsql \
    zip \
    pcntl

USER www-data
