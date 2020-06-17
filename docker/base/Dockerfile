FROM ubuntu:20.04
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-base" \
    org.label-schema.description="Docker base image which is used for Autopilot builds." \
    org.label-schema.url="https://github.com/sitepilot/autopilot-base"

# Build arguments
ARG PHP_VER="74"
ARG WEBSERVER_USER_ID=1000
ARG WEBSERVER_USER_GID=1000
ARG WEBSERVER_USER_NAME=autopilot

ARG DEBIAN_FRONTEND=noninteractive

# Environment variables
ENV SET_VHOST_FILE_PERMISSIONS="yes"

ENV PHP_VER=$PHP_VER
ENV PHP_MEMORY_LIMIT="256M"
ENV PHP_UPLOAD_MAX_FILESIZE="32M"
ENV PHP_POST_MAX_SIZE="64M"
ENV PHP_DISPLAY_ERRORS="on"
ENV PHP_TIMEZONE="Europe/Amsterdam"
ENV PHP_LSAPI_CHILDREN=10

ENV WEBSERVER_USER_NAME=$WEBSERVER_USER_NAME
ENV WEBSERVER_USER_ID=$WEBSERVER_USER_ID
ENV WEBSERVER_USER_GID=$WEBSERVER_USER_GID
ENV WEBSERVER_SERVER_NAME="webserver"
ENV WEBSERVER_SSL_CERT="/autopilot/conf/cert/default.crt"
ENV WEBSERVER_SSL_KEY="/autopilot/conf/cert/default.key"
ENV WEBSERVER_TRUSTED_IPS="173.245.48.0/20T, 103.21.244.0/22T, 103.22.200.0/22T, 103.31.4.0/22T, 141.101.64.0/18T, 108.162.192.0/18T, 190.93.240.0/20T, 188.114.96.0/20T, 197.234.240.0/22T, 198.41.128.0/17T, 162.158.0.0/15T, 104.16.0.0/12T, 172.64.0.0/13T, 131.0.72.0/22T, 2400:cb00::/32T, 2606:4700::/32T, 2803:f800::/32T, 2405:b500::/32T, 2405:8100::/32T, 2a06:98c0::/29T, 2c0f:f248::/32T"
ENV WEBSERVER_CACHE="off"

# When using Composer, disable the warning about running commands as root/super user
ENV COMPOSER_ALLOW_SUPERUSER=1

# Enable openlitespeed repository
RUN apt-get update \
    && apt-get install -y wget software-properties-common \
    && wget -O - http://rpms.litespeedtech.com/debian/enable_lst_debain_repo.sh | bash

# Persistent runtime dependencies
ARG DEPS="\
    ansible \
    lsphp$PHP_VER \
    lsphp$PHP_VER-mysql \
    lsphp$PHP_VER-imap \
    lsphp$PHP_VER-curl \
    lsphp$PHP_VER-common \
    lsphp$PHP_VER-json \
    lsphp$PHP_VER-redis \
    lsphp$PHP_VER-intl \
    openlitespeed \
    supervisor \
    git \
    curl \
    runit \
    nano \
    perl \
    cron \
    ssmtp \
    less \
    sudo \
    unzip \
    "

# Install packages
RUN set -e \
    && apt-get update \
    && apt-get install -y $DEPS \
    && ln -s /usr/local/lsws/lsphp$PHP_VER/bin/php /usr/local/bin/php

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"

# Install PHPunit
RUN wget https://phar.phpunit.de/phpunit.phar \
    && chmod +x phpunit.phar \
    && mv phpunit.phar /usr/local/bin/phpunit

# Add configuration files
COPY tags /
RUN chmod -R +x /autopilot/bin/*
RUN chmod -R +x /etc/service/*

# Clean up APT when done
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Setup user
RUN addgroup --gid "$WEBSERVER_USER_GID" "$WEBSERVER_USER_NAME" \
    && adduser \
    --disabled-password \
    --gecos "" \
    --home "/var/www" \
    --ingroup "$WEBSERVER_USER_NAME" \
    --no-create-home \
    --uid "$WEBSERVER_USER_ID" \
    "$WEBSERVER_USER_NAME" \
    && usermod -aG sudo $WEBSERVER_USER_NAME \
    && echo "${WEBSERVER_USER_NAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Expose ports
EXPOSE 80
EXPOSE 443

# Set workdir
WORKDIR /var/www

# Set user
USER $WEBSERVER_USER_NAME

# Set volumes
VOLUME ["/var/www"]

# Set entrypoint
ENTRYPOINT ["sudo", "-E", "/autopilot/bin/entrypoint"]

# Start services
CMD ["/autopilot/bin/runit-wrapper"]
