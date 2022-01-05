FROM php:7.4-fpm-buster

ARG UID
ARG GID

RUN usermod -u $UID www-data \
    && groupmod -g $GID www-data

RUN apt-get update \
    && apt-get install -y git

RUN apt-get update \
    && apt-get install -y libmagickwand-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick

RUN apt-get update && \
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-install gd

RUN apt-get update \
    && apt-get install -y libzip-dev zip \
    && docker-php-ext-install zip

RUN pecl install redis \
    && docker-php-ext-enable redis

RUN docker-php-ext-install opcache

RUN docker-php-ext-install bcmath

RUN docker-php-ext-install pcntl

RUN apt-get update \
    && apt-get install -y libgmp-dev \
    && docker-php-ext-install gmp

RUN docker-php-ext-install pcntl

RUN docker-php-ext-install pdo

RUN docker-php-ext-install pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN mkdir -p /var/www/.composer \
    && chown www-data:www-data /var/www/.composer

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY php.ini $PHP_INI_DIR/conf.d/custom.ini

RUN mkdir -p /var/www/.config \
    && chown www-data:www-data /var/www/.config

WORKDIR /var/www/sourcecode/website

USER www-data
