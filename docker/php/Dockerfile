FROM php:8.5.5-fpm-alpine3.23

ENV TZ=America/Sao_Paulo \
    COMPOSER_ALLOW_SUPERUSER=1

# Dependências do sistema
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    tzdata \
    icu-dev \
    postgresql-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

# Extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_pgsql \
    intl \
    mbstring \
    gd

# PHP config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Cache de dependências
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --no-progress

COPY . .

EXPOSE 9000

CMD ["php-fpm"]