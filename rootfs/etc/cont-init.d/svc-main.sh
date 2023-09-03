#!/usr/bin/with-contenv sh

TZ=${TZ:-UTC}
MEMORY_LIMIT=${MEMORY_LIMIT:-256M}
POST_MAX_SIZE=${POST_MAX_SIZE:-16M}
UPLOAD_MAX_FILESIZE=${UPLOAD_MAX_FILESIZE:-16M}
OPCACHE_MEM_SIZE=${OPCACHE_MEM_SIZE:-128}
OPCACHE_ENABLE=${OPCACHE_ENABLE:-1}
REAL_IP_FROM=${REAL_IP_FROM:-0.0.0.0/32}
REAL_IP_HEADER=${REAL_IP_HEADER:-X-Forwarded-For}
LOG_IP_VAR=${LOG_IP_VAR:-remote_addr}

# Timezone
echo "Setting timezone to ${TZ}..."
ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime
echo ${TZ} > /etc/timezone

# PHP-FPM
echo "Setting PHP-FPM configuration..."
sed -e "s/@MEMORY_LIMIT@/$MEMORY_LIMIT/g" \
  -e "s/@POST_MAX_SIZE@/$POST_MAX_SIZE/g" \
  -e "s/@UPLOAD_MAX_FILESIZE@/$UPLOAD_MAX_FILESIZE/g" \
  /tpls/etc/php7/php-fpm.d/www.conf > /etc/php7/php-fpm.d/www.conf

echo "Setting php.ini configuration..."
sed -i "s|memory_limit.*|memory_limit = ${MEMORY_LIMIT}|g" /etc/php7/php.ini
sed -i "s|;date\.timezone.*|date\.timezone = ${TZ}|g" /etc/php7/php.ini

# OpCache
echo "Setting OpCache configuration..."
sed -e "s/@OPCACHE_MEM_SIZE@/$OPCACHE_MEM_SIZE/g" \
  -e "s/@OPCACHE_ENABLE@/$OPCACHE_ENABLE/g" \
  /tpls/etc/php7/conf.d/opcache.ini > /etc/php7/conf.d/opcache.ini

# Nginx
echo "Setting Nginx configuration..."
sed -e "s#@REAL_IP_FROM@#$REAL_IP_FROM#g" \
  -e "s#@REAL_IP_HEADER@#$REAL_IP_HEADER#g" \
  -e "s#@LOG_IP_VAR@#$LOG_IP_VAR#g" \
  -e "s#@POST_MAX_SIZE@#$POST_MAX_SIZE#g" \
  /tpls/etc/nginx/nginx.conf > /etc/nginx/nginx.conf

mkdir -p /etc/services.d/nginx
cat > /etc/services.d/nginx/run <<EOL
#!/usr/bin/execlineb -P
with-contenv
s6-setuidgid ${PUID}:${PGID}
nginx -g "daemon off;"
EOL
chmod +x /etc/services.d/nginx/run

mkdir -p /etc/services.d/php-fpm
cat > /etc/services.d/php-fpm/run <<EOL
#!/usr/bin/execlineb -P
with-contenv
s6-setuidgid ${PUID}:${PGID}
php-fpm7 -F
EOL
chmod +x /etc/services.d/php-fpm/run
