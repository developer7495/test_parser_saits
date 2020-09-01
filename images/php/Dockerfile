FROM php:7.4-fpm

RUN apt-get update && apt-get install -y curl \
	&& apt-get install -y git \
    && apt-get autoremove -y --purge \
    && apt-get clean \
    && pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR /usr/share/nginx/html
CMD ["php-fpm"]
