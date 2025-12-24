ARG NODE_VERSION=24.11.1

ARG PHP_VERSION=8.5.1
ARG PHP_MEMORY_LIMIT=512M

ARG UPLOAD_MAX_FILESIZE_IN_BYTES=52428800

ARG MERCURE_VERSION=0.21.2

## NodeJS base image
FROM node:${NODE_VERSION}-alpine AS node

## Mercure base image
FROM dunglas/mercure:v${MERCURE_VERSION} AS mercure

### NodeJS build ###
FROM node:${NODE_VERSION}-alpine AS build-client
WORKDIR /client

COPY services/client/package.json services/client/yarn.lock ./

RUN yarn install --frozen-lockfile --non-interactive --network-timeout 900000

COPY services/client/plugins ./plugins
COPY services/client/src ./src
COPY services/client/static ./static
COPY services/client/svelte.config.js services/client/tsconfig.json services/client/vite.config.ts ./

RUN yarn svelte-kit sync && \
    NODE_OPTIONS="--max-old-space-size=2048 --expose-gc" yarn build

RUN rm -rf node_modules && \
    yarn install --frozen-lockfile --non-interactive --production --network-timeout 900000
### End of NodeJS build ###

### PHP source ###
FROM alpine AS source
WORKDIR /source

COPY services/api/bin ./bin
COPY services/api/config ./config
COPY services/api/public ./public
COPY services/api/shared ./shared
COPY services/api/src ./src
COPY services/api/composer.json ./
### End of PHP source ###

### PHP vendor ###
FROM php:${PHP_VERSION}-alpine AS vendor
WORKDIR /dependencies

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY services/api/composer.json services/api/composer.lock ./

RUN composer install \
    --ignore-platform-reqs \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts
### End of PHP vendor ###

### PHP extensions build ###
FROM php:${PHP_VERSION}-alpine AS php-extensions
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    git \
    autoconf \
    g++ \
    make \
    linux-headers \
    curl-dev \
    libmcrypt-dev \
    icu-dev \
    imagemagick-dev \
    vips-dev \
    postgresql-dev \
    libpng-dev \
    libwebp-dev \
    tiff-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libheif-dev \
    oniguruma-dev \
    samba-dev \
    brotli-dev \
    libffi-dev

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    curl \
    intl \
    mbstring \
    exif \
    ffi \
    redis \
    smbclient \
    vips 

## Replace this after proper support for PHP 8.5 is added (only master branch currently)
RUN git clone --branch master --single-branch https://github.com/swoole/swoole-src.git && \
    cd swoole-src && \
    git checkout bca833cf2170aced3ee9453313ace9db56cdd1f9 && \
    sed -i 's/PHP_VERSION_ID > 80500/PHP_VERSION_ID >= 80600/' ext-src/php_swoole_private.h && \
    phpize && \
    ./configure --enable-openssl --enable-http2 --enable-swoole-curl CXXFLAGS="-fpermissive" && \
    make && make install && \
    cd .. && rm -rf swoole-src

RUN docker-php-ext-enable swoole

### End of PHP extensions build ###

### Base build stage ###
FROM php:${PHP_VERSION}-alpine AS base

RUN apk update && apk upgrade &&\
    apk add --no-cache \
    redis \
    libmcrypt \
    libcurl \
    icu-libs \
    libsmbclient \
    vips \
    imagemagick \
    libheif \
    supervisor

COPY --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY --from=mercure /usr/bin/caddy /usr/local/bin/mercure

RUN echo "ffi.enable=1" > /usr/local/etc/php/conf.d/zz-ffi-enable.ini

COPY ./docker/config/supervisord.conf /etc/supervisord.conf

COPY ./docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

COPY ./docker/scripts/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

COPY ./docker/scripts/slink /usr/local/bin/slink
RUN chmod +x /usr/local/bin/slink

ARG PHP_MEMORY_LIMIT
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini

ARG UPLOAD_MAX_FILESIZE_IN_BYTES
RUN echo "upload_max_filesize = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" >> /usr/local/etc/php/conf.d/uploads.ini

RUN apk add --no-cache tzdata
RUN echo 'date.timezone = ${TZ}' > /usr/local/etc/php/conf.d/timezone.ini

WORKDIR /services

ENV API_URL=http://localhost:8080
ENV API_PREFIX=/api
ENV PROXY_PREFIXES="/api;/image"
ENV ORIGIN=http://localhost:3000
ENV TZ=UTC

CMD ["/usr/local/bin/entrypoint.sh"]
### End of base build stage ###

### Test image ###
FROM php:${PHP_VERSION}-alpine AS test

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /services/api

COPY services/api/composer.json services/api/composer.lock ./

RUN composer install --ignore-platform-reqs --no-interaction --no-scripts

COPY --chown=www-data:www-data ./services/api .

RUN mv .env.example .env
ENV APP_ENV=test

ARG PHP_MEMORY_LIMIT
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini
### End of test image ###

### Development image ###
FROM base AS dev
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .build-deps

RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=/dev/null" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony

COPY docker/config/runtime/develop.conf /etc/supervisor/conf.d/develop.conf
COPY --from=node /usr/local/bin /usr/local/bin
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /opt /opt
COPY services/api/.env.example ./api/.env

ENV API_ENABLED=true
ENV APP_ENV=dev
ENV NODE_ENV=development
ENV SWOOLE_ENABLED=false
ENV PHP_IDE_CONFIG="serverName=localhost"

EXPOSE 5173
EXPOSE 8080
### End of development image ###

### Production image ###
FROM base AS prod
RUN docker-php-ext-enable swoole

COPY docker/config/runtime/production.conf /etc/supervisor/conf.d/production.conf
COPY --from=node /usr/local/bin/node /usr/local/bin/node

RUN addgroup -g 1000 slink && \
    adduser -D -u 1000 -G slink slink

COPY --from=vendor --chown=slink:slink /dependencies/vendor ./api/vendor
COPY --from=source --chown=slink:slink /source ./api
COPY --from=build-client --chown=slink:slink /client/build ./client/svelte-kit
COPY --from=build-client --chown=slink:slink /client/package.json ./client/svelte-kit/package.json
COPY --from=build-client --chown=slink:slink /client/node_modules ./client/svelte-kit/node_modules
COPY --chown=slink:slink services/api/.env.example ./api/.env

RUN mkdir -p /app/var/data && \
    mkdir -p /app/slink && \
    chown -R slink:slink /app/var /app/slink

ENV API_ENABLED=false
ENV APP_ENV=prod
ENV NODE_ENV=production
ENV SWOOLE_ENABLED=true

ARG UPLOAD_MAX_FILESIZE_IN_BYTES
ENV BODY_SIZE_LIMIT=${UPLOAD_MAX_FILESIZE_IN_BYTES}

EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:8080/api/health || exit 1
### End of production image ###