FROM php:8.1-apache

RUN docker-php-ext-install mysqli

# Install MongoDB extension
RUN apt-get update && apt-get install -y \
    libssl-dev \
    pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb
COPY . /var/www/html/

EXPOSE 80
