FROM composer:1.5 as builder

COPY . /app/
WORKDIR /app

RUN composer install -o \
      --prefer-dist --ignore-platform-reqs \
      --no-ansi --no-interaction --no-scripts

RUN php -d 'phar.readonly = 0' /app/vendor/bin/phar-composer build .

FROM php:7.1-alpine
WORKDIR /
COPY --from=builder /app/fpm-health-check.phar /
COPY --from=builder /app/fpm-health-check.fcgi.php /
