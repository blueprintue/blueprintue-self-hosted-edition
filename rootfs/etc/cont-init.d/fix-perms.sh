#!/usr/bin/with-contenv sh

echo "Fixing perms..."
mkdir -p /opt/blueprintue-self-hosted-edition/storage \
  /opt/blueprintue-self-hosted-edition/www/medias \
  /opt/blueprintue-self-hosted-edition/www/medias/avatars \
  /opt/blueprintue-self-hosted-edition/www/medias/posts \
  /opt/blueprintue-self-hosted-edition/www/medias/blueprints \
  /var/run/nginx \
  /var/run/php-fpm
chown blueprintue-self-hosted-edition. \
  /opt/blueprintue-self-hosted-edition/.env \
  /opt/blueprintue-self-hosted-edition/storage \
  /opt/blueprintue-self-hosted-edition/www/medias \
  /opt/blueprintue-self-hosted-edition/www/medias/avatars \
  /opt/blueprintue-self-hosted-edition/www/medias/posts \
  /opt/blueprintue-self-hosted-edition/www/medias/blueprints
chown -R blueprintue-self-hosted-edition. \
  /tpls \
  /var/lib/nginx \
  /var/log/nginx \
  /var/log/php7 \
  /var/run/nginx \
  /var/run/php-fpm
