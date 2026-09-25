ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install calendar intl zip \
    && pecl install igbinary \
    && docker-php-ext-enable igbinary \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/memory.ini

WORKDIR /app
