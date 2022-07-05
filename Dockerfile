FROM ghcr.io/roadrunner-server/roadrunner:2.10.5 AS roadrunner
FROM php:8.1-cli-alpine

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.29/install-php-extensions /usr/local/bin/

ARG CONFIG=/var/www/prod.rr.yaml

ADD . /var/www/
WORKDIR /var/www

EXPOSE 8080

HEALTHCHECK CMD curl -f http://127.0.0.1:2114/health?plugin=http&plugin=rpc || exit 1

RUN apk add --update --no-cache \
    curl \
    openssl-dev \
    libzip-dev && \
    apk add --update --no-cache --virtual buildDeps \
            autoconf \
            gcc \
            make \
            libxml2-dev \
            wget \
            tzdata \
            curl-dev \
            oniguruma-dev \
            g++ && \
    pecl install mongodb && \
    docker-php-ext-install mysqli pdo_mysql bcmath ftp zip pcntl sockets && \
    docker-php-ext-enable mongodb && \
    chmod +x /usr/local/bin/install-php-extensions && \
    sync && \
    install-php-extensions redis-stable && \
    rm /usr/local/bin/install-php-extensions && \
    wget https://getcomposer.org/download/latest-2.x/composer.phar --output-document=/usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    apk del buildDeps && \
    ln -s /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    composer install --no-dev && \
    rm /usr/local/bin/composer && \
    cp "$CONFIG" "/var/www/running.rr.yaml"

CMD ["rr", "serve", "-c", "/var/www/running.rr.yaml", "-w", "/var/www/", "-o=http.address=:8080"]