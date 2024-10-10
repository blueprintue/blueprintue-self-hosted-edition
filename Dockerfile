FROM crazymax/alpine-s6:3.19 AS base

ENV S6_BEHAVIOR_IF_STAGE2_FAILS="2" \
  TZ="UTC" \
  PUID="1500" \
  PGID="1500"

RUN apk --update --no-cache add \
    curl \
    nginx \
    php \
    php-cli \
    php-ctype \
    php-curl \
    php-dom \
    php-exif \
    php-fileinfo \
    php-fpm \
    php-gd \
    php-iconv \
    php-intl \
    php-json \
    php-mbstring \
    php-opcache \
    php-openssl \
    php-pdo \
    php-pdo_mysql \
    php-phar \
    php-session \
    php-sodium \
    php-xml \
    php-zlib \
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
