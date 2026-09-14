FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions required for Symfony & PostgreSQL/MySQL
RUN apk add --no-linux-headers --no-cache \
    bash \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    git \
    unzip

RUN docker-php-ext-install \
    intl \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
