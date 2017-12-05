#
# Composer install
#
FROM composer:1.5 as php-builder

COPY . /app/
RUN composer install -o \
      --prefer-dist --no-dev --ignore-platform-reqs \
      --no-ansi --no-interaction --no-scripts

WORKDIR /app
