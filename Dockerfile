FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    mysql-client \
    git \
    curl \
    unzip \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    libwebp-dev \
    freetype-dev \
    gmp-dev \
    oniguruma-dev \
    icu-dev \
    postgresql-dev \
    make \
    autoconf \
    g++ \
    linux-headers

RUN docker-php-ext-install \
    pdo_mysql \
    zip \
    gd \
    gmp \
    mbstring \
    intl \
    opcache \
    pcntl \
    bcmath \
    sockets

RUN rm -rf /var/cache/apk/*

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
