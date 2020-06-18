FROM caddy:alpine
MAINTAINER Sitepilot <support@sitepilot.io>

LABEL org.label-schema.vendor="Sitepilot" \
    org.label-schema.name="autopilot-base" \
    org.label-schema.description="Docker base image which is used to build Autopilot." \
    org.label-schema.url="https://github.com/sitepilot/autopilot"

EXPOSE 80
EXPOSE 443

ARG AUTOPILOT_USER_ID=1000
ARG AUTOPILOT_USER_GID=1000
ARG AUTOPILOT_USER_NAME=autopilot

ARG AUTOPILOT_PROMETHEUS_VERSION=2.19.0
ARG AUTOPILOT_ALERTMANAGER_VERSION=0.21.0
ARG AUTOPILOT_BLACKBOX_VERSION=0.16.0

ENV AUTOPILOT_URL="https://127.0.0.1"
ENV AUTOPILOT_CERT_EMAIL="internal"
ENV AUTOPILOT_MONITOR_PASSWORD="supersecret"
ENV AUTOPILOT_USER_ID=$AUTOPILOT_USER_ID
ENV AUTOPILOT_USER_GID=$AUTOPILOT_USER_GID
ENV AUTOPILOT_USER_NAME=$AUTOPILOT_USER_NAME

ENV AUTOPILOT_MONITOR_SLACK_WEBHOOK="https://hooks.slack.com/services/xxxx/xxxx/xxxx"

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
    && rm -rf /tmp/* /var/cache/apk/*

COPY tags /
RUN chmod +x /autopilot/bin/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');"

RUN wget https://phar.phpunit.de/phpunit.phar \
    && chmod +x phpunit.phar \
    && mv phpunit.phar /usr/local/bin/phpunit

RUN addgroup --gid "$AUTOPILOT_USER_GID" "$AUTOPILOT_USER_NAME" \
    && adduser \
    --disabled-password \
    --gecos "" \
    --home "/var/www" \
    --ingroup "$AUTOPILOT_USER_NAME" \
    --no-create-home \
    --uid "$AUTOPILOT_USER_ID" \
    "$AUTOPILOT_USER_NAME" \
    && echo "${AUTOPILOT_USER_NAME} ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

RUN curl -LO https://github.com/prometheus/prometheus/releases/download/v$AUTOPILOT_PROMETHEUS_VERSION/prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64.tar.gz \
    && tar xvf prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64.tar.gz \
    && mv prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64/prometheus /usr/local/bin/ \
    && mv prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64/promtool /usr/local/bin/ \
    && mv prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64/consoles /etc/prometheus/ \
    && mv prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64/console_libraries /etc/prometheus/ \
    && rm -rf prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64.tar.gz prometheus-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64

RUN curl -LO https://github.com/prometheus/alertmanager/releases/download/v$AUTOPILOT_ALERTMANAGER_VERSION/alertmanager-$AUTOPILOT_ALERTMANAGER_VERSION.linux-amd64.tar.gz \
    && tar xvf alertmanager-$AUTOPILOT_ALERTMANAGER_VERSION.linux-amd64.tar.gz \
    && mv alertmanager-$AUTOPILOT_ALERTMANAGER_VERSION.linux-amd64/alertmanager /usr/local/bin/ \
    && mv alertmanager-$AUTOPILOT_ALERTMANAGER_VERSION.linux-amd64/amtool /usr/local/bin/ \
    && rm -rf alertmanager-$AUTOPILOT_PROMETHEUS_VERSION.linux-amd64.tar.gz alertmanager-$AUTOPILOT_ALERTMANAGER_VERSION.linux-amd64

RUN curl -LO https://github.com/prometheus/blackbox_exporter/releases/download/v$AUTOPILOT_BLACKBOX_VERSION/blackbox_exporter-$AUTOPILOT_BLACKBOX_VERSION.linux-amd64.tar.gz \
    && tar xvf blackbox_exporter-$AUTOPILOT_BLACKBOX_VERSION.linux-amd64.tar.gz \
    && mv blackbox_exporter-$AUTOPILOT_BLACKBOX_VERSION.linux-amd64/blackbox_exporter /usr/local/bin/ \
    && rm -rf blackbox_exporter-$AUTOPILOT_BLACKBOX_VERSION.linux-amd64.tar.gz blackbox_exporter-$AUTOPILOT_BLACKBOX_VERSION.linux-amd64

RUN echo "Set disable_coredump false" >> /etc/sudo.conf

WORKDIR /var/www

USER autopilot

ENTRYPOINT ["sudo", "--preserve-env", "/autopilot/bin/entrypoint"]

CMD ["sudo", "--preserve-env", "supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]