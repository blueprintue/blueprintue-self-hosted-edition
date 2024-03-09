# Examples of docker-compose

## Pull Docker Image from GitHub Registry
```shell
docker login ghcr.io
docker pull ghcr.io/blueprintue/blueprintue-self-hosted-edition:edge
docker-compose pull
```

## Docker Compose variations
For each files you will need to find and replace values `_____REPLACE_ME_____` with what you need.

### Basic
You will have in that file [docker-compose-localhost.yml]:
- traefik
  - open on port 80
  - use `traefik-http.yml`
- mariadb
  - on init use `dump-with-anonymous-user.sql`
  - use `database.env`
- maildev
  - all emails sent are visible on `localhost:1080`

### HTTPS
You will have in that file [docker-compose-https.yml]:
- traefik
  - open on port 80 and 443
  - use `traefik-https.yml` with let's encrypt challenge for OVH
  - use `traefik-https.env` with OVH
- mariadb
  - on init use `dump-with-anonymous-user.sql`
  - use `database.env`
- maildev
  - all emails sent are visible on `localhost:1080`

### SMTP
You will have in that file [docker-compose-smtp.yml]:
- traefik
  - open on port 80
  - use `traefik-http.yml`
- mariadb
  - on init use `dump-with-anonymous-user.sql`
  - use `database.env`
- msmtpd
  - emails will be sent using smtp relay
  - use `msmtpd.env`

If you want to use Gmail or any other mail provider that need TLS you need to set `MAIL_USE_SMTP_TLS=true`.

## Env variables
### Rootfs
#### User rights
* `PUID` user id
* `PGID` group id

#### Timezone
* `TZ` timezone (by default: UTC)

#### PHP-FPM
* `MEMORY_LIMIT` memory limit (by default: 256M)
* `POST_MAX_SIZE` post max size (by default: 16M)
* `UPLOAD_MAX_SIZE` upload max size (by default: 16M)

#### OPCache
* `OPCACHE_ENABLE` opcache enable (by default: 1)
* `OPCACHE_MEM_SIZE` opcache memory consumption (by default: 128)

#### Nginx
* `REAL_IP_FROM` real ip from (by default: 0.0.0.0/32)
* `REAL_IP_HEADER` real ip header (by default: X-Forwarded-For)
* `LOG_IP_VAR` log ip var (by default: remote_addr)

## Docker Buildx Commands
* `docker buildx bake` create image-local
* `docker buildx bake validate` launch 2 subtasks vendor-update && vendor-validate
* `docker buildx bake vendor-validate` check if there is a drift with composer.lock
* `docker buildx bake lint` check if code is matching with lint rules
* `docker buildx bake test` end 2 end testing
* `docker buildx bake image` create a docker image for registry
* `docker buildx bake image-local` create a local docker image
