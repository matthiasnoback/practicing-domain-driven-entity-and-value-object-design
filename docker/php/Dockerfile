FROM php:7.2-alpine
RUN apk add --no-cache $PHPIZE_DEPS --virtual .build-deps \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps
COPY xdebug.ini ${PHP_INI_DIR}/conf.d/xdebug.ini
WORKDIR /opt/project
