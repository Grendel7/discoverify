FROM php:7.3-cli AS composer

COPY . /mnt

# Install Composer
RUN apt-get update && apt-get install -y --no-install-recommends git unzip zip \
    && curl https://getcomposer.org/composer.phar > /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && cd /mnt \
    && composer install --no-dev --optimize-autoloader --ignore-platform-reqs

FROM node:12 as mix

COPY . /mnt

RUN cd /mnt \
    && yarn install \
    && yarn run prod

FROM php:7.3-apache

RUN docker-php-ext-install pdo_mysql opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

RUN a2enmod rewrite remoteip ;\
    {\
     echo RemoteIPHeader X-Real-IP ;\
     echo RemoteIPTrustedProxy 10.0.0.0/8 ;\
     echo RemoteIPTrustedProxy 172.16.0.0/12 ;\
     echo RemoteIPTrustedProxy 192.168.0.0/16 ;\
    } > /etc/apache2/conf-available/remoteip.conf;\
    a2enconf remoteip \
    && sed -i '/DocumentRoot/c\DocumentRoot \/var\/www\/html\/public' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html
COPY --from=composer /mnt/vendor /var/www/html/vendor
COPY --from=mix /mnt/public /var/www/html/public

ENV LOG_CHANNEL=errorlog

RUN chown -R www-data:www-data /var/www/html/storage \
    && php artisan package:discover
