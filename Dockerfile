FROM crazymax/alpine-s6:3.13 AS base

ENV S6_BEHAVIOR_IF_STAGE2_FAILS="2" \
  TZ="UTC" \
  PUID="1500" \
  PGID="1500"

RUN apk --update --no-cache add \
    nginx \
    php7 \
    php7-cli \
    php7-ctype \
    php7-curl \
    php7-dom \
    php7-exif \
    php7-fileinfo \
    php7-fpm \
    php7-gd \
    php7-iconv \
    php7-intl \
    php7-json \
    php7-mbstring \
    php7-opcache \
    php7-openssl \
    php7-pdo \
    php7-pdo_mysql \
    php7-phar \
    php7-session \
    php7-sodium \
    php7-xml \
    php7-zlib \
    mariadb-client \
    shadow \
    tzdata \
  && addgroup -g ${PGID} blueprintue-self-hosted-edition \
  && adduser -D -h /opt/blueprintue-self-hosted-edition -u ${PUID} -G blueprintue-self-hosted-edition -s /bin/sh -D blueprintue-self-hosted-edition \
  && rm -rf /tmp/*

FROM base AS blueprintue-self-hosted-edition
WORKDIR /opt/blueprintue-self-hosted-edition
COPY composer.* .
RUN apk --update --no-cache add curl \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
  && composer validate \
  && COMPOSER_CACHE_DIR="/tmp" composer install --optimize-autoloader --no-dev --no-interaction --no-ansi \
  && chown -R blueprintue-self-hosted-edition. /opt/blueprintue-self-hosted-edition
COPY app ./app
COPY www ./www
COPY .env.template ./.env

FROM base

COPY --from=blueprintue-self-hosted-edition --chown=blueprintue-self-hosted-edition:blueprintue-self-hosted-edition /opt/blueprintue-self-hosted-edition /opt/blueprintue-self-hosted-edition
COPY rootfs /
RUN chmod +x /etc/cont-init.d/fix-logs.sh && chmod +x /etc/cont-init.d/fix-perms.sh && chmod +x /etc/cont-init.d/svc-main.sh

EXPOSE 8000
WORKDIR /opt/blueprintue-self-hosted-edition
VOLUME [ "/opt/blueprintue-self-hosted-edition/storage" ]

ENTRYPOINT ["/init"]
