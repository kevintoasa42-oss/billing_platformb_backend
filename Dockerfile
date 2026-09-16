FROM php:8.4-fpm-alpine@sha256:6cb5e4ffa03a7c1b01bb5b120ab3684ef76b75aa5ca417e343936db3f71f419f

# Apply current Alpine security fixes while keeping the immutable base digest,
# then install only the runtime/build dependencies required by PHP extensions.
RUN apk upgrade --no-cache \
    && apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    linux-headers \
    openjdk17-jre \
    openssl \
    libxml2-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
       pdo \
       pdo_pgsql \
       bcmath \
       zip \
       intl \
       gd \
       opcache \
       pcntl \
       soap \
    && apk del $PHPIZE_DEPS linux-headers

# Composer
COPY --from=composer:2@sha256:4d71c3c2109c61d5415544264b59ad4087e4c5b7244481723664138fd36d5040 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php-security.ini /usr/local/etc/php/conf.d/zz-billing-security.ini

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy project files
COPY . .

# Permissions for Laravel storage
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy entrypoint
COPY docker/entrypoint-app.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
