# syntax=docker/dockerfile:1.2
ARG PHP_VERSION

FROM php:$PHP_VERSION-cli-alpine AS base
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True

RUN apk --update --no-cache add \
  build-base \
  curl \
  freetype-dev \
  git \
  jpeg-dev \
  libjpeg-turbo-dev \
  libpng-dev \
  libwebp-dev \
  libxpm-dev \
  mariadb \
  mariadb-client \
  musl-dev \
  python3-dev \
  zlib-dev \
  linux-headers
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
  && composer --version
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN pecl install xdebug && docker-php-ext-enable xdebug
RUN docker-php-ext-install gd pdo_mysql
RUN echo 'memory_limit = -1' >> $PHP_INI_DIR/conf.d/php.ini
WORKDIR /src

FROM base AS vendored
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True
RUN --mount=type=bind,target=.,rw \
  --mount=type=cache,target=/src/vendor \
  composer validate \
  && composer install --no-interaction --no-ansi \
  && mkdir /out \
  && cp composer.lock /out

FROM scratch AS vendor-update
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True
COPY --from=vendored /out /

FROM vendored AS vendor-validate
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True
RUN --mount=type=bind,target=.,rw \
  git add -A && cp -Rf /out/* .; \
  if [ -n "$(git status --porcelain -- composer.lock)" ]; then \
    echo >&2 'ERROR: Vendor result differs. Please vendor your package with "docker buildx bake vendor-update"'; \
    git status --porcelain -- composer.lock; \
    exit 1; \
  fi

FROM vendored AS lint
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True
RUN --mount=type=bind,target=.,rw \
  --mount=type=cache,target=/src/vendor \
  composer lint-validate

FROM vendored AS test
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PHP_CS_FIXER_IGNORE_ENV=True
COPY . .
RUN composer install --no-interaction --no-ansi \
  && mkdir tests/storage_test \
  && mkdir tests/medias
ENTRYPOINT ["composer"]
