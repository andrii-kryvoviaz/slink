FROM composer:2 as vendor
# Copy composer files
COPY composer.json composer.lock ./

# Install composer dependencies
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --prefer-dist


FROM php:8.2-alpine as base
# Install dependencies
RUN apk update && apk upgrade &&\
    apk add --no-cache \
    supervisor \
    libmcrypt \
    libcurl \
    libpng \
    libjpeg-turbo \
    freetype \
    imagemagick \
    postgresql-libs \
    icu-libs

# Install Common PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS autoconf g++ make linux-headers imagemagick-dev curl-dev postgresql-dev icu-dev libpng-dev libmcrypt-dev libjpeg-turbo-dev oniguruma-dev && \
    docker-php-ext-configure gd && \
    docker-php-ext-install curl intl mysqli pdo_pgsql mbstring gd && \
    pecl install mcrypt redis imagick && \
    docker-php-ext-enable mcrypt redis imagick && \
    apk del .build-deps

# Copy supervisor config
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Set working directory
WORKDIR /app

EXPOSE 80

# Run entrypoint
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]


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

# Set environment variables
ENV APP_ENV=dev
ENV SWOOLE_ENABLED=false
ENV PHP_IDE_CONFIG="serverName=localhost"

# Use Symfony web server
COPY docker/runtime/develop.conf /etc/supervisor/conf.d/develop.conf


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

# Change owner of project files
RUN chown -R www-data:www-data /app

# Set environment variables
ENV APP_ENV=prod
ENV SWOOLE_ENABLED=true

# Use Swoole Runtime
COPY docker/runtime/production.conf /etc/supervisor/conf.d/production.conf
