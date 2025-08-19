# Self-hosted install with Nginx without Docker

Authors: [@unquietwiki](https://github.com/unquietwiki)

## Introduction

The basic instructions for using this software, assume you are either using Docker, or uploading to a shared Apache host.

These instructions are geared towards a fully self-hosted install, using Nginx. This example assumes you are doing a fresh install, using a recent version of [Nginx](https://nginx.org/en/), and [Debian 12 Linux](https://www.debian.org/) or [Ubuntu 24.04 Linux](https://www.ubuntu.com/). For editing text files, we'll assume you'll be using [nano](https://www.nano-editor.org/).

Actual system requirements are quite low. This example was largely developed on an AWS [**t4g.medium** ARM64 instance](https://aws.amazon.com/ec2/instance-types/t4/). AWS provides a `admin` user with sudo-privileges; you should have a similar user on your own setup.

## Installing pre-requisites

First of all, you need to be up to date with apt, and add the necessary repos that will provide more current version of PHP.

```shell
sudo apt update
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
```

The PHP PPA author also maintains an active Nginx installer, with a fairly recent version. If you wish to use the official release instead, disregard this step, [follow those instructions](https://nginx.org/en/linux_packages.html#instructions), and use ``nginx`` in place of ``www-data`` in subsequent commands.

```shell
sudo add-apt-repository ppa:ondrej/nginx -y
```

Then you can install packages:
```shell
sudo apt install nginx php8.4-fpm mariadb-server mariadb-client composer php-curl php-fpm php-gd php-mbstring php-mysql php-xml -y
```

If you're planning on hosting this on an external HTTPS website, you'll need to [setup Let's Encrypt](https://linuxcapable.com/how-to-secure-nginx-with-lets-encrypt-on-debian-linux/); please adjust for the new config file location.

## Loading the software

### Software install

Make a location to extract BlueprintUE to. For this example, we'll use `/opt/blueprintue`; download and extract the latest version (check the releases page & update the "wget" command accordingly) to that location:
```shell
cd /opt
wget https://github.com/blueprintue/blueprintue-self-hosted-edition/archive/refs/tags/v4.1.0.tar.gz
tar xvf v4.1.0.tar.gz
mv blueprintue-self-hosted-edition-4.1.0 blueprintue
rm v4.1.0.tar.gz
chown -R admin:www-data /opt/blueprintue
useradd -m -s /bin/bash admin
su - admin
cd /opt/blueprintue && composer install
exit
chown -R admin:www-data /opt/blueprintue
```

### MariaDB import database
Connect to MariaDB with mysql command:
```shell
mysql -u root
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
Then you comment the line `$env->enableCache();` with `//` and save.

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
Update values of `user`, `group`, `listen.owner`, and `listen.group` to equal `www-data`.
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

    access_log  /var/log/nginx/host.access.log;

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
