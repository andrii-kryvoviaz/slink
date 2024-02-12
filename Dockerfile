ARG NODE_VERSION=20.9.0

ARG PHP_VERSION=8.3.2
ARG PHP_MEMORY_LIMIT=512M

ARG UPLOAD_MAX_FILESIZE_IN_BYTES=52428800

## NodeJS image
## Used to build client app and provide NodeJS binaries to PHP image
FROM node:${NODE_VERSION}-alpine as node


FROM node as node-dependencies
WORKDIR /

COPY client/package.json client/yarn.lock ./
RUN yarn install --frozen-lockfile --non-interactive --production=true && \
    yarn add vite

COPY client ./

RUN yarn build


## Source backand files
FROM alpine as source
WORKDIR /source

COPY bin ./bin
COPY config ./config
COPY public ./public
COPY src ./src
COPY composer.json ./


## Composer image
## Used to install project dependencies
FROM composer:2 as vendor
WORKDIR /dependencies

# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --prefer-dist


## Base image
## Used to run application and provide base for development and production images
FROM php:${PHP_VERSION}-alpine as base
# Install dependencies
RUN apk update && apk upgrade &&\
    apk add --no-cache \
    supervisor \
    libmcrypt \
    libcurl \
    libsmbclient \
    imagemagick \
    libjpeg-turbo \
    libpng \
    libwebp \
    freetype \
    postgresql-libs

# Install Common PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS git autoconf g++ make linux-headers curl-dev libmcrypt-dev imagemagick-dev postgresql-dev libpng-dev libwebp-dev freetype-dev libjpeg-turbo-dev oniguruma-dev samba-dev && \
    docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp && \
    docker-php-ext-install curl mysqli pdo_pgsql mbstring gd exif && \
    pecl install mcrypt smbclient && \
    # Imagick PHP 8.3 bug (https://github.com/Imagick/imagick/pull/641)
    git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick && \
    cd /tmp/imagick && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    rm -rf /tmp/imagick && \
    # End of Imagick fix
    docker-php-ext-enable mcrypt imagick smbclient && \
    apk del .build-deps

# Copy supervisor config
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Copy startup script
COPY ./docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Set memory limit
ARG PHP_MEMORY_LIMIT
RUN echo "memory_limit=${PHP_MEMORY_LIMIT}" > /usr/local/etc/php/conf.d/memory-limit.ini

# Set upload max filesize
ARG UPLOAD_MAX_FILESIZE_IN_BYTES
RUN echo "upload_max_filesize = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = $((UPLOAD_MAX_FILESIZE_IN_BYTES / 1024 / 1024))M" >> /usr/local/etc/php/conf.d/uploads.ini

# Set working directory
WORKDIR /app

# Create .env file
COPY .env.example .env

# Run entrypoint
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]


## Development image
## Used to run application in development mode
FROM base as dev
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
FROM base as prod
# Install Production PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers && \
    pecl install swoole && \
    docker-php-ext-enable swoole opcache && \
    apk del .build-deps

# Use Swoole Runtime
COPY docker/runtime/production.conf /etc/supervisor/conf.d/production.conf

# Copy node executable
COPY --from=node /usr/local/bin/node /usr/local/bin/node

# Copy backend app
COPY --from=source --chown=www-data:www-data /source ./

# Copy vendor files
COPY --from=vendor /dependencies/vendor ./vendor

# Copy client app
COPY --from=node-dependencies --chown=www-data:www-data /build ./svelte-kit
COPY --from=node-dependencies /package.json ./svelte-kit/package.json

# Create data directory for database storage
RUN mkdir -p /app/var/data

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
