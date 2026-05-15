FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html
