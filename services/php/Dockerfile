FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && docker-php-ext-install mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY ./services/php/php.ini /usr/local/etc/php/php.ini

RUN mkdir -p /var/log/php \
    && touch /var/log/php/error.log \
    && chown -R www-data:www-data /var/log/php \
    && chmod 777 /var/log/php/error.log

WORKDIR /var/www/html

COPY . .

RUN composer install
