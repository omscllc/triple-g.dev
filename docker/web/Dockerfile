ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-fpm-alpine

ARG WEB_INTERNAL_PORT=80
ENV WEB_INTERNAL_PORT=${WEB_INTERNAL_PORT}

# Install Nginx and runtime deps for Drupal.
RUN apk update && apk add --no-cache \
    git \
    patch \
    unzip \
    zip \
    composer \
    nodejs \
    npm \
    nginx \
    fcgi \
    bash \
    gettext \
    icu-dev \
    libzip-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libavif-dev \
    oniguruma-dev \
    mysql-client \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-avif \
    && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql \
    mysqli \
    intl \
    zip \
    gd \
    && rm -rf /var/cache/apk/*

RUN mkdir -p /run/nginx /var/log/nginx /var/www/html /var/run/php \
    && chown -R nginx:nginx /run/nginx /var/log/nginx /var/run/php

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

COPY docker/web/nginx.conf.template /etc/nginx/templates/default.conf.template
COPY docker/web/php-fpm-socket.conf /usr/local/etc/php-fpm.d/zz-docker-socket.conf
COPY docker/web/mariadb-client.cnf /etc/my.cnf.d
COPY docker/web/entrypoint.sh /entrypoint.sh
COPY docker/00-php.ini /usr/local/etc/php/conf.d

RUN chmod +x /entrypoint.sh

WORKDIR /var/www/html

RUN git config --global --add safe.directory '*'

EXPOSE ${WEB_INTERNAL_PORT}

ENTRYPOINT ["/entrypoint.sh"]