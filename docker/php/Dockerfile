FROM php:8.2-fpm

RUN set -eux; \
    apt-get update; \
    apt-get upgrade -y; \
    apt-get install -y --no-install-recommends \
            curl \
            libmemcached-dev \
            libz-dev \
            libpq-dev \
            libjpeg-dev \
            libpng-dev \
            libfreetype6-dev \
            libssl-dev \
            libwebp-dev \
            libxpm-dev \
            libmcrypt-dev \
            libonig-dev \
            libicu-dev; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    curl -sL https://deb.nodesource.com/setup_14.x | bash -; \
    apt-get install -y nodejs npm;

RUN set -eux; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-configure gd \
            --prefix=/usr \
            --with-jpeg \
            --with-webp \
            --with-xpm \
            --with-freetype; \
    docker-php-ext-install gd; \
    docker-php-ext-install intl;

# Install Composer
RUN set -eux; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


WORKDIR /var/www/html

COPY . /var/www/html