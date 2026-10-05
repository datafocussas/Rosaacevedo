FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libwebp-dev libicu-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql gd intl zip exif bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN echo "memory_limit=512M\nupload_max_filesize=10M\npost_max_size=12M" > /usr/local/etc/php/conf.d/rosa.ini
