FROM caddy:alpine
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot" \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

EXPOSE 80
EXPOSE 443

ARG AUTOPILOT_USER_ID=1001
ARG AUTOPILOT_USER_GID=1001
ARG AUTOPILOT_USER_NAME=autopilot

ENV AUTOPILOT_CERT_EMAIL="internal"
ENV AUTOPILOT_MONITOR_PASSWORD="supersecret"
ENV AUTOPILOT_USER_ID=$AUTOPILOT_USER_ID
ENV AUTOPILOT_USER_GID=$AUTOPILOT_USER_GID
ENV AUTOPILOT_USER_NAME=$AUTOPILOT_USER_NAME

RUN apk add --update \
        bash \
        sudo \
        ansible \
        shadow \
        php7-fpm \
        php7-cli \
        php7-mbstring \
        php7-iconv \
        php7-imap \
        php7-common \
        php7-curl \
        php7-redis \
        php7-json \
        php7-openssl \
        php7-phar \
        php7-soap \
        php7-zip \
        php7-xml \
        php7-opcache \
        php7-pdo_mysql \
        php7-pcntl \
        php7-posix \
        php7-dom \
        php7-xmlwriter \
        php7-tokenizer \
        supervisor \
        openssh \
        curl \
        wget \
        nano \
        tar \
        git \
    && rm -rf /tmp/* /var/cache/apk/*

COPY docker/autopilot/tags /
RUN chmod +x /autopilot/bin/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"

RUN addgroup --gid "$AUTOPILOT_USER_GID" "$AUTOPILOT_USER_NAME" \
    && adduser \
    --disabled-password \
    --gecos "" \
    --home "/var/www" \
    --ingroup "autopilot" \
    --no-create-home \
    --uid "$AUTOPILOT_USER_ID" \
    "$AUTOPILOT_USER_NAME" \
    && echo "${AUTOPILOT_USER_NAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN echo "Set disable_coredump false" >> /etc/sudo.conf

COPY . /var/www/html

USER autopilot

WORKDIR /var/www

ENTRYPOINT ["sudo", "--preserve-env", "/autopilot/bin/entrypoint"]

CMD ["sudo", "--preserve-env", "supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
