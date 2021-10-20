FROM postgres:14.0 as postgres
ADD temp.sql /docker-entrypoint-initdb.d
FROM php:8.0-fpm-alpine
RUN set -ex \
  && apk --no-cache add \
    postgresql-dev apache2
   

RUN docker-php-ext-install pdo pdo_pgsql


RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer

WORKDIR /todoListLaravelReactApp
COPY . /todoListLaravelReactApp

RUN composer install --no-cache

CMD php artisan serve --host=0.0.0.0 --port=8000