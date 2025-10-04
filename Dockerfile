## Production Image
# Author: Pavlo Maksymov

FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock* ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-interaction

FROM dunglas/frankenphp:1.9.1-php8.4-alpine AS runtime

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    COMPOSER_ALLOW_SUPERUSER=1 \
    SERVER_NAME=:80

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY . /app

COPY --from=composer /app/vendor /app/vendor

RUN set -eux; \
    mkdir -p var/cache var/log /data/caddy; \
    chown -R www-data:www-data var && \
    chown -R www-data:www-data /data && \
    chown -R www-data:www-data /app

USER www-data

RUN set -eux; \
    composer dump-autoload --no-dev --classmap-authoritative; \
    php bin/console cache:clear --env=prod --no-warmup; \
    php bin/console cache:warmup --env=prod

EXPOSE 80

CMD ["frankenphp", "run", "--config=/etc/caddy/Caddyfile"]

