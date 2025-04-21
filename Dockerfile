FROM crazymax/alpine-s6:3.21 AS base

ENV S6_BEHAVIOR_IF_STAGE2_FAILS="2" \
  TZ="UTC" \
  PUID="1500" \
  PGID="1500"

RUN apk --update --no-cache add \
    curl \
    nginx \
    php84 \
    php84-cli \
    php84-ctype \
    php84-curl \
    php84-dom \
    php84-exif \
    php84-fileinfo \
    php84-fpm \
    php84-gd \
    php84-iconv \
    php84-intl \
    php84-json \
    php84-mbstring \
    php84-opcache \
    php84-openssl \
    php84-pdo \
    php84-pdo_mysql \
    php84-phar \
    php84-session \
    php84-sodium \
    php84-xml \
    php84-zlib \
    mariadb-client \
    shadow \
    tzdata \
  && addgroup -g ${PGID} blueprintue-self-hosted-edition \
  && adduser -D -h /opt/blueprintue-self-hosted-edition -u ${PUID} -G blueprintue-self-hosted-edition -s /bin/sh -D blueprintue-self-hosted-edition \
  && rm -rf /tmp/* \
  && ln -s /usr/bin/php84 /usr/bin/php

FROM base AS blueprintue-self-hosted-edition
WORKDIR /opt/blueprintue-self-hosted-edition
COPY composer.* .
RUN apk --update --no-cache add curl \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
  && composer validate \
  && COMPOSER_CACHE_DIR="/tmp" composer install --optimize-autoloader --no-dev --no-interaction --no-ansi \
  && chown -R blueprintue-self-hosted-edition /opt/blueprintue-self-hosted-edition
COPY app ./app
COPY www ./www
RUN touch .env

FROM base

COPY --from=blueprintue-self-hosted-edition --chown=blueprintue-self-hosted-edition:blueprintue-self-hosted-edition /opt/blueprintue-self-hosted-edition /opt/blueprintue-self-hosted-edition
COPY rootfs /
RUN chmod +x /etc/cont-init.d/fix-logs.sh && chmod +x /etc/cont-init.d/fix-perms.sh && chmod +x /etc/cont-init.d/svc-main.sh

EXPOSE 8000
WORKDIR /opt/blueprintue-self-hosted-edition
VOLUME [ "/opt/blueprintue-self-hosted-edition/storage" ]

COPY cronscript.sh /etc/periodic/15min/crons
CMD [ "crond", "-l", "2", "-f" ]

ENTRYPOINT ["/init"]
