FROM php:8.1.6-fpm

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql

RUN pecl install -o -f redis \
    && docker-php-ext-enable redis.so

RUN pecl install xdebug-3.1.5 \
    && docker-php-ext-enable xdebug

RUN apt-get install -y libssl-dev librabbitmq-dev \
    && pecl install amqp \
    && docker-php-ext-enable amqp.so

RUN docker-php-ext-install sockets

RUN apt-get update -y && apt-get install -y libxml2-dev \
    && docker-php-ext-install soap

ADD php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /app