ARG NODE_VERSION=24.13.1
ARG PHP_VERSION=8.5.3

FROM node:${NODE_VERSION}-alpine AS node

### NodeJS build ###
FROM node:${NODE_VERSION}-alpine AS deps-client-prod
WORKDIR /client

COPY services/client/package.json services/client/yarn.lock ./
RUN yarn install --frozen-lockfile --non-interactive --production && \
    yarn cache clean && \
    find node_modules -name "*.md" -o -name "*.ts" -o -name "*.map" | xargs rm -f 2>/dev/null || true

FROM node:${NODE_VERSION}-alpine AS build-client
WORKDIR /client

COPY services/client/package.json services/client/yarn.lock ./
RUN yarn install --frozen-lockfile --non-interactive

COPY services/client/plugins ./plugins
COPY services/client/src ./src
COPY services/client/static ./static
COPY services/client/svelte.config.js services/client/tsconfig.json services/client/vite.config.ts ./

RUN yarn svelte-kit sync && \
    NODE_OPTIONS="--max-old-space-size=2048 --expose-gc" yarn build

### PHP source ###
FROM alpine:3.21 AS source
WORKDIR /source
COPY services/api/bin ./bin
COPY services/api/config ./config
COPY services/api/public ./public
COPY services/api/shared ./shared
COPY services/api/src ./src
COPY services/api/composer.json ./

### PHP vendor ###
FROM composer:2 AS vendor
WORKDIR /dependencies
COPY services/api/composer.json services/api/composer.lock ./
RUN composer install \
    --ignore-platform-reqs \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts && \
    composer clear-cache

### PHP extensions build ###
FROM dunglas/frankenphp:php${PHP_VERSION}-alpine AS php-extensions

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    curl \
    intl \
    mbstring \
    exif \
    ffi \
    redis \
    smbclient \
    vips && \
    rm -rf /tmp/* /var/cache/apk/*

### Base build stage ###
FROM dunglas/frankenphp:php${PHP_VERSION}-alpine AS base

ARG MEMORY_LIMIT=512
ARG UPLOAD_MAX_FILESIZE_IN_BYTES=52428800

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache \
        redis \
        libmcrypt \
        libcurl \
        icu-libs \
        libsmbclient \
        imagemagick \
        vips \
        libheif \
        supervisor \
        tzdata && \
    rm -rf /tmp/* /var/cache/apk/*

COPY --link --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --link --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

RUN <<EOF
echo "ffi.enable=1" > /usr/local/etc/php/conf.d/zz-ffi-enable.ini
echo "memory_limit=${MEMORY_LIMIT}M" > /usr/local/etc/php/conf.d/memory-limit.ini
cat > /usr/local/etc/php/conf.d/uploads.ini <<UPLOADS
upload_max_filesize = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M
post_max_size = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M
UPLOADS
echo 'date.timezone = ${TZ}' > /usr/local/etc/php/conf.d/timezone.ini
EOF

COPY --link ./docker/config/supervisord.conf /etc/supervisord.conf
COPY --link ./docker/config/caddy/Caddyfile /etc/caddy/Caddyfile
COPY --link ./docker/config/caddy/Caddyfile.mercure /etc/caddy/Caddyfile.mercure
COPY --link --chmod=755 ./docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY --link --chmod=755 ./docker/scripts/startup.sh /usr/local/bin/startup.sh
COPY --link --chmod=755 ./docker/scripts/slink /usr/local/bin/slink

WORKDIR /services

ENV API_URL=http://localhost:8080 \
    API_PREFIX=/api \
    PROXY_PREFIXES="/api;/image" \
    ORIGIN=http://localhost:3000 \
    TZ=UTC \
    # FrankenPHP recource limits
    GOMEMLIMIT=${MEMORY_LIMIT}MiB \
    GOMAXPROCS=8 \
    # VIPS settings
    VIPS_CONCURRENCY=4 

CMD ["/usr/local/bin/entrypoint.sh"]

### Test image ###
FROM php:${PHP_VERSION}-alpine AS test

ARG MEMORY_LIMIT=512

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /services/api

RUN apk add --no-cache git unzip && \
    echo "memory_limit=${MEMORY_LIMIT}M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY services/api/composer.json services/api/composer.lock ./
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts

COPY --chown=www-data:www-data ./services/api .
RUN mv .env.example .env

ENV APP_ENV=test

### Development image ###
FROM base AS dev

RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .build-deps && \
    rm -rf /tmp/* /var/cache/apk/*

RUN cat >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini <<EOF
xdebug.mode=debug
xdebug.client_host=host.docker.internal
xdebug.log=/dev/null
xdebug.idekey=PHPSTORM
xdebug.start_with_request=yes
EOF

COPY --link --from=ghcr.io/symfony-cli/symfony-cli:latest /usr/local/bin/symfony /usr/local/bin/symfony
COPY --link docker/config/runtime/develop.conf /etc/supervisor/conf.d/develop.conf
COPY --link --from=node /usr/local/bin /usr/local/bin
COPY --link --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --link --from=node /opt /opt
COPY services/api/.env.example ./api/.env

ENV API_ENABLED=true \
    APP_ENV=dev \
    NODE_ENV=development \
    PHP_IDE_CONFIG="serverName=localhost"

EXPOSE 5173 8080

### Production image ###
FROM base AS prod

ARG UPLOAD_MAX_FILESIZE_IN_BYTES=52428800

COPY --link docker/config/runtime/production.conf /etc/supervisor/conf.d/production.conf
COPY --link --from=node /usr/local/bin/node /usr/local/bin/node

RUN addgroup -g 1000 slink && \
    adduser -D -u 1000 -G slink slink && \
    mkdir -p /app/var/data /app/slink /data/caddy && \
    chown -R slink:slink /app/var /app/slink /data/caddy

COPY --from=vendor --chown=slink:slink /dependencies/vendor ./api/vendor
COPY --from=source --chown=slink:slink /source ./api
COPY --from=build-client --chown=slink:slink /client/build ./client/svelte-kit
COPY --from=build-client --chown=slink:slink /client/package.json ./client/svelte-kit/package.json
COPY --from=deps-client-prod --chown=slink:slink /client/node_modules ./client/svelte-kit/node_modules
COPY --chown=slink:slink services/api/.env.example ./api/.env

ENV API_ENABLED=false \
    APP_ENV=prod \
    NODE_ENV=production \
    BODY_SIZE_LIMIT=${UPLOAD_MAX_FILESIZE_IN_BYTES}

EXPOSE 3000

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD wget --no-verbose --tries=1 --spider http://127.0.0.1:8080/api/health || exit 1
