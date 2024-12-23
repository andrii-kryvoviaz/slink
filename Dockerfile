ARG NODE_VERSION=20.9.0

ARG PHP_VERSION=8.4.1
ARG PHP_MEMORY_LIMIT=512M

ARG UPLOAD_MAX_FILESIZE_IN_BYTES=52428800

## NodeJS image
## Used to build client app and provide NodeJS binaries to PHP image
FROM node:${NODE_VERSION}-alpine AS node


# Prevent Docker from invalidating cache when package.json version changes
# Since it is not used in the build process
FROM node AS plain-package-json
COPY client/package.json client/yarn.lock ./
RUN yarn version --new-version 0.0.0


FROM node AS node-dependencies
WORKDIR /

COPY --from=plain-package-json /package.json /yarn.lock ./

RUN yarn install --frozen-lockfile --non-interactive --production=true && \
    yarn add vite

# Copy client source files
COPY client/plugins ./plugins
COPY client/src ./src
COPY client/static ./static
COPY client/svelte.config.js client/tsconfig.json client/vite.config.ts client/postcss.config.js client/tailwind.config.ts ./

# Build client app
RUN yarn build


## Source backend files
FROM alpine AS source
WORKDIR /source

COPY bin ./bin
COPY config ./config
COPY public ./public
COPY src ./src
COPY composer.json ./


## Composer image
## Used to install project dependencies
FROM composer:2 AS vendor
WORKDIR /dependencies

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --prefer-dist


## Base image
## Used to run application and provide base for development and production images
FROM php:${PHP_VERSION}-alpine AS base
# Install dependencies
RUN apk update && apk upgrade &&\
    apk add --no-cache \
    supervisor \
    redis \
    libmcrypt \
    libcurl \
    icu-libs \
    libsmbclient \
    imagemagick \
    libjpeg-turbo \
    libheif \
    libpng \
    libwebp \
    tiff \
    freetype \
    postgresql-libs

# Install Common PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS git autoconf g++ make linux-headers curl-dev libmcrypt-dev icu-dev imagemagick-dev postgresql-dev libpng-dev libwebp-dev tiff-dev freetype-dev libjpeg-turbo-dev libheif-dev oniguruma-dev samba-dev && \
    docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp && \
    docker-php-ext-install curl intl mysqli pdo_pgsql mbstring gd exif && \
    pecl install redis smbclient && \
    # Imagick PHP 8.3 bug (https://github.com/Imagick/imagick/pull/641)
    git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick && \
    cd /tmp/imagick && \
    sed -i 's/php_strtolower/zend_str_tolower/g' imagick.c && \
    phpize && \
    ./configure --with-heic=yes --with-jpeg=yes --with-png=yes --with-webp=yes --with-tiff=yes && \
    make && \
    make install && \
    rm -rf /tmp/imagick && \
    # End of Imagick fix
    docker-php-ext-enable imagick redis smbclient && \
    apk del .build-deps

# Copy supervisor config
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Copy startup script
COPY ./docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Copy Slink executable
COPY ./docker/slink /usr/local/bin/slink
RUN chmod +x /usr/local/bin/slink

# Set memory limit
ARG PHP_MEMORY_LIMIT
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini

# Set upload max filesize
ARG UPLOAD_MAX_FILESIZE_IN_BYTES
RUN echo "upload_max_filesize = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" >> /usr/local/etc/php/conf.d/uploads.ini

# Set current timezone
RUN apk add --no-cache tzdata
RUN echo 'date.timezone = ${TZ}' > /usr/local/etc/php/conf.d/timezone.ini

# Set working directory
WORKDIR /app

# Set environment variables
ENV API_URL=http://localhost:8080
ENV API_PREFIX=/api
ENV PROXY_PREFIXES="/api;/image"
ENV ORIGIN=http://localhost:3000

# Run entrypoint
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]


# Test image
## A lightweight image used to run unit tests and linting
FROM php:${PHP_VERSION}-alpine AS test
# Copy composer executable
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install development dependencies
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts

# Copy backend app
COPY --chown=www-data:www-data . .

# Set environment variables
RUN mv .env.example .env
ENV APP_ENV=test

# Set memory limit
ARG PHP_MEMORY_LIMIT
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini


## Development image
## Used to run application in development mode
FROM base AS dev
# Install Development PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .build-deps

# Configure Xdebug
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=/app/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | sh
RUN apk add --no-cache symfony-cli

# Use Symfony web server
COPY docker/runtime/develop.conf /etc/supervisor/conf.d/develop.conf

# Add NodeJS executables
COPY --from=node /usr/local/bin /usr/local/bin

# Add NPM dependencies
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules

# Add Yarn dependencies
COPY --from=node /opt /opt

# Create .env file
COPY .env.example .env

# Set environment variables
ENV API_ENABLED=true
ENV APP_ENV=dev
ENV NODE_ENV=development
ENV SWOOLE_ENABLED=false
ENV PHP_IDE_CONFIG="serverName=localhost"

# Expose client app port
EXPOSE 5173
EXPOSE 8080


## Production image
## Used to run application in production mode
FROM base AS prod
# Install Production PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers brotli-dev && \
    pecl install swoole && \
    docker-php-ext-enable swoole opcache && \
    apk del .build-deps

# Use Swoole Runtime
COPY docker/runtime/production.conf /etc/supervisor/conf.d/production.conf

# Copy node executable
COPY --from=node /usr/local/bin/node /usr/local/bin/node

# Copy vendor files
COPY --from=vendor /dependencies/vendor ./vendor

# Copy client app
COPY --from=node-dependencies --chown=www-data:www-data /build ./svelte-kit
COPY --from=node-dependencies /package.json ./svelte-kit/package.json

# Copy backend app
COPY --from=source --chown=www-data:www-data /source ./

# Create data directory for database storage
RUN mkdir -p /app/var/data

# Create .env file
COPY .env.example .env

# Set environment variables
ENV API_ENABLED=false
ENV APP_ENV=prod
ENV NODE_ENV=production
ENV SWOOLE_ENABLED=true

# Set Node Adapter Size Limit
ARG UPLOAD_MAX_FILESIZE_IN_BYTES
ENV BODY_SIZE_LIMIT=${UPLOAD_MAX_FILESIZE_IN_BYTES}

# Expose client app port
EXPOSE 3000
