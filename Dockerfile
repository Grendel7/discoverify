FROM php:7.3-cli

RUN docker-php-ext-install pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install NodeJS (for Laravel Mix)
RUN apt-get update \
    && apt-get install -y gnupg \
    && curl -sL https://deb.nodesource.com/setup_10.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /root/.cache /root/.npm

# Install Composer
RUN curl https://getcomposer.org/composer.phar > /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

# Set the non-root user
RUN useradd -ms /bin/bash docker
USER docker

# Setup environment
EXPOSE 8000
WORKDIR /home/docker/html
CMD ["php", "artisan", "tinker"]
