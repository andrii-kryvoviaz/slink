ARG NODE_VERSION=20.9.0

ARG PHP_VERSION=8.2.12
ARG PHP_MEMORY_LIMIT=512M

## NodeJS image
## Used to build client app and provide NodeJS binaries to PHP image
FROM node:${NODE_VERSION}-alpine as node


FROM node as node-dependencies
WORKDIR /temp

COPY client/package.json client/yarn.lock ./
RUN yarn install --frozen-lockfile --non-interactive --production=true && \
    yarn add vite

COPY client ./

RUN yarn build


## Composer image
## Used to install project dependencies
FROM composer:2 as vendor
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
    postgresql-libs

# Install Common PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers curl-dev libmcrypt-dev imagemagick-dev postgresql-dev libpng-dev libjpeg-turbo-dev oniguruma-dev samba-dev && \
    docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install curl mysqli pdo_pgsql mbstring gd exif && \
    pecl install mcrypt imagick smbclient && \
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

# Add NodeJS
COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

# Set working directory
WORKDIR /app

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

# Install Yarn
RUN npm install -g yarn --force

# Set environment variables
ENV API_ENABLED=true
ENV APP_ENV=dev
ENV NODE_ENV=development
ENV SWOOLE_ENABLED=false
ENV PHP_IDE_CONFIG="serverName=localhost"

# Use Symfony web server
COPY docker/runtime/develop.conf /etc/supervisor/conf.d/develop.conf

# Expose client app port
EXPOSE 5173


## Production image
## Used to run application in production mode
FROM base as prod
# Install Production PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers && \
    pecl install swoole && \
    docker-php-ext-enable swoole opcache && \
    apk del .build-deps

# Copy files
COPY bin ./bin
COPY config ./config
COPY public ./public
COPY src ./src
COPY .env composer.json ./

# Copy vendor files
COPY --from=vendor /app/vendor/ ./vendor/

# Copy client app
COPY --from=node-dependencies /temp/build ./client
COPY --from=node-dependencies /temp/package.json ./client/package.json

# Set environment variables
ENV API_ENABLED=false
ENV APP_ENV=prod
ENV NODE_ENV=production
ENV SWOOLE_ENABLED=true

## Node doesn't have to verify SSL certificates within the container
ENV NODE_TLS_REJECT_UNAUTHORIZED=0
# Set Node Adapter Size Limit to 50MB
ENV BODY_SIZE_LIMIT=52428800

# Set database URLs
ENV DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/slink.db
ENV ES_DATABASE_URL=sqlite:///%kernel.project_dir%/var/data/slink_store.db

# Set storage provider
ENV STORAGE_PROVIDER=local

# Use Swoole Runtime
COPY docker/runtime/production.conf /etc/supervisor/conf.d/production.conf

# Expose client app port
EXPOSE 3000
