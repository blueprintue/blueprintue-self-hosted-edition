# Self-hosted install with Nginx without Docker

Authors: [@unquietwiki](https://github.com/unquietwiki)

## Introduction

The basic instructions for using this software, assume you are either using Docker, or uploading to a shared Apache host.

These instructions are geared towards a fully self-hosted install, using Nginx. This example assumes you are doing a fresh install, using the latest version of [Nginx](https://nginx.org/en/), and [Debian 12 Linux](https://www.debian.org/). For editing text files, we'll assume you'll be using [nano](https://www.nano-editor.org/).

Actual system requirements are quite low. This example was largely developed on an AWS [**t4g.medium** ARM64 instance](https://aws.amazon.com/ec2/instance-types/t4/). AWS provides a `admin` user with sudo-privileges; you should have a similar user on your own setup.

## Installing pre-requisites

[Nginx installation](https://nginx.org/en/linux_packages.html#Debian); note, the vendor-provided Nginx uses `/etc/nginx/conf.d` instead of `sites-available` or `sites-enabled` for site configuration. We'll be using the `default.conf` for this install.

If you're planning on hosting this on an external HTTPS website, you'll need to [setup Let's Encrypt](https://linuxcapable.com/how-to-secure-nginx-with-lets-encrypt-on-debian-linux/); please adjust for the new config file location.

You'll also need PHP 8.4 and MariaDB.

First of all, you need to be up to date with apt:
```shell
sudo apt update
```

Then you can install packages:
```shell
sudo apt install -y mariadb-server mariadb-client php-composer php-curl php-fpm php-gd php-mbstring php-mysql php-xml
```

## Loading the software

### Software install

Make a location to extract BlueprintUE to; it can even be a mount on an external partition (compressed BTRFS is a good use for this). For this example, we'll use `/opt/blueprintue`; download and extract the latest version to that location:
```shell
cd /opt/blueprintue && composer install
```

This has to be done outside of root/sudo. Afterwards, you'll want to change the owner:
```shell
chown -R admin:nginx /opt/blueprintue
```

### MariaDB import database
Connect to MariaDB with mysql command:
```shell
mysql
```

Create database, user and permissions:
```shell
CREATE DATABASE blueprintue;
GRANT ALL PRIVILEGES ON blueprintue.* TO 'blueprintue'@localhost IDENTIFIED BY 'randompassword';
exit;
```

Now you can import the dump file:
```shell
mysql -u blueprintue -p blueprintue < dump-with-anonymous-user.sql
```

### Setup .env file

#### Disabling env caching file

You can disable the caching env file:
```shell
nano www/index.php
```
Then you comment the line `$env->enableCache();` and save.

Finally you can remove the `.env.cache.php` if it was generated:
```shell
rm .env.cache.php
```

#### Fill .env file

You copy the `.env.template` to `.env`:
```shell
cp .env.template .env
```

Then you set the values there accordingly (see README.md for more details); be sure to account for using HTTPS or not.
```shell
nano .env
```

## Configuration of PHP and Nginx

### PHP
Update values of `user`, `group`, `listen.owner`, and `listen.group` to equal `nginx`.
```shell
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

When the configuration is done, make sure PHP fpm is running:
```shell
sudo systemctl enable php8.4-fpm.service
sudo systemctl restart php8.4-fpm.service
```

### Nginx

The following is a basic `default.conf` for Nginx; not including the HTTPS changes you may have made before. You'll need to adjust accordingly. An external tool was used to convert valid `.htaccess` rules to Nginx format.

```nginx
server {
    listen       80;
    listen       [::]:80;
    server_name  blueprintue.example.com;
    root   /opt/blueprintue/www;
    index index.php;

    access_log  /var/log/nginx/host.access.log  main;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    autoindex off;

    charset utf-8;

    location ~ ^.*\.([Hh][Tt][Aa]) {
        deny all;
    }

    location ~ (\.env|\.env.template|\.env\.cache\.php)$ {
        deny all;
    }

    location /error_log {
        deny all;
    }

    location /xmlrpc.php {
        deny all;
    }

    location ~ \.php$ {
        include        fastcgi_params;
        fastcgi_pass   unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    location ~ /\.ht {
        deny  all;
    }
}
```

Also, for `/etc/nginx/nginx.conf`, add the following to the http section:

```nginx
    client_body_buffer_size     8M;
    client_max_body_size        8M;
```

When the configuration is done, make sure Nginx is running:
```shell
sudo systemctl enable nginx
sudo systemctl restart nginx
```

## Conclusion

At this point, you should have a largely working setup. You may need to tweak some additional things or permissions to make things work correctly. Instructions from the `README.md` will help in this regard.
