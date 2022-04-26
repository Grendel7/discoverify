FROM php:8.1-cli

RUN apt-get update \
    && apt-get install zip unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /root/.cache

RUN docker-php-ext-install pdo_mysql \
    && pecl install xdebug redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable xdebug

# Install NodeJS (for Laravel Mix)
RUN apt-get update \
    && apt-get install -y gnupg \
    && curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
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
