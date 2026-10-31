# Production image. Builds frontend assets and PHP dependencies in
# throwaway stages so the final image carries no Node, no Composer, and
# no dev dependencies — just PHP-FPM, nginx, and the app.

FROM composer:2 AS vendor-build
WORKDIR /app
COPY composer.json composer.lock ./
COPY database/ database/
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize

# Needs vendor/tightenco/ziggy (a Composer package whose JS/route-helper
# file resources/js/app.ts imports directly), so this stage runs after
# vendor-build rather than in parallel with it.
FROM node:22-alpine AS frontend-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor-build /app/vendor/tightenco vendor/tightenco
RUN npm run build

FROM php:8.5-fpm-alpine AS runtime

# opcache is deliberately left out: on php:8.5-fpm-alpine, `docker-php-ext-install
# opcache` builds but never produces modules/opcache.so ("Build complete"
# followed by "cp: can't stat 'modules/*'"), with or without JIT enabled —
# looks like an upstream musl/8.5 toolchain issue, not something fixable
# from this Dockerfile. Tracked as a follow-up; the app runs correctly
# without it, just without opcode caching.
RUN apk add --no-cache \
        nginx supervisor postgresql-client tar \
        icu-libs libzip libpng oniguruma \
    && apk add --no-cache --virtual .build-deps \
        icu-dev libzip-dev libpng-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-install \
        pdo_pgsql pgsql mbstring bcmath gd zip intl \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=vendor-build /app /var/www/html
COPY --from=frontend-build /app/public/build /var/www/html/public/build

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
