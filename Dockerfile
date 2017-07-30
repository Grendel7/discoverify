# PHP 7.0 base image
FROM php:7.0-cli

# Install PHP extensions
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libwebp-dev libcurl3-dev libmysqlclient-dev libxml2-dev git && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN docker-php-ext-install gd curl json xml pdo_mysql zip

RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install and upgrade NPM (for Laravel Mix)
RUN apt-get update && apt-get install -y nodejs npm && npm install -g npm && npm install -g n && n 7.10.0 && ln -sf /usr/local/n/versions/node/7.10.0/bin/node /usr/bin/node && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /root/.cache /root/.npm

# Install Composer
RUN curl https://getcomposer.org/composer.phar > /usr/local/bin/composer && chmod +x /usr/local/bin/composer

# Set the non-root user
RUN useradd -ms /bin/bash docker
USER docker

# Setup environment
EXPOSE 8000
WORKDIR /home/docker/html
