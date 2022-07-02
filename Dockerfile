FROM ghcr.io/roadrunner-server/roadrunner:2.X.X AS roadrunner
FROM php:8.1-cli

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.29/install-php-extensions /usr/local/bin/

ADD . /var/www/
WORKDIR /var/www

RUN apk add --update --no-cache \
    openssl-dev \
    libzip-dev && \
    apk add --update --no-cache --virtual buildDeps \
            autoconf \
            gcc \
            make \
            libxml2-dev \
            curl \
            tzdata \
            curl-dev \
            oniguruma-dev \
            g++ && \
    pecl install mongodb && \
    docker-php-ext-install mysqli pdo_mysql bcmath ftp zip pcntl && \
    docker-php-ext-enable mongodb && \
    chmod +x /usr/local/bin/install-php-extensions && \
    sync && \
    install-php-extensions redis-stable && \
    rm /usr/local/bin/install-php-extensions && \
    apk del buildDeps && \
    ln -s /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    composer install --no-dev