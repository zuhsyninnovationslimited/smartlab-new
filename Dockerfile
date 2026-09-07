FROM node:22-alpine AS frontend-build

WORKDIR /build/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY frontend/ ./
RUN npm run build


FROM composer:2 AS php-dependencies

WORKDIR /build/api
COPY smartlab-api/composer.json smartlab-api/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader


FROM php:8.2-apache-bookworm AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PORT=8080

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libcurl4-openssl-dev \
        libonig-dev \
        libpq-dev \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        mbstring \
        opcache \
        pdo_mysql \
        pdo_pgsql \
    && a2dismod -f mpm_event mpm_worker mpm_prefork \
    && rm -f /etc/apache2/mods-enabled/mpm_*.load \
        /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork \
    && test -L /etc/apache2/mods-enabled/mpm_prefork.load \
    && test "$(find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' | wc -l)" -eq 1 \
    && a2enmod expires headers rewrite \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY smartlab-api/ ./
COPY --from=php-dependencies /build/api/vendor/ ./vendor/
COPY --from=frontend-build /build/frontend/dist/ ./public/
COPY docker/apache-security.conf /etc/apache2/conf-available/smartlab-security.conf
COPY docker/app-entrypoint.sh /usr/local/bin/smartlab-entrypoint

RUN a2enconf smartlab-security \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/smartlab-entrypoint \
    && apache2ctl configtest

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=45s --retries=3 \
    CMD curl -fsS "http://127.0.0.1:${PORT:-8080}/up" || exit 1

ENTRYPOINT ["smartlab-entrypoint"]
CMD ["apache2-foreground"]
