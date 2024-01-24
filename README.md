# blueprintUE self-hosted edition

![PHP Version Support](https://img.shields.io/badge/%3E%3D7.4.0-777BB4?label=php)
![MariaDB Version Support](https://img.shields.io/badge/%3E%3D10.6-003545?label=MariaDB)
![MySQL Version Support](https://img.shields.io/badge/%3E%3D8-005C84?label=MySQL)
![Use Docker](https://img.shields.io/badge/Docker-0db7ed)
[![Composer dependencies](https://img.shields.io/badge/dependencies-9-brightgreen)](https://github.com/blueprintue/blueprintue-self-hosted-edition/blob/main/composer.json)
[![Test workflow](https://img.shields.io/github/actions/workflow/status/blueprintue/blueprintue-self-hosted-edition/validate.yml?branch=main)](https://github.com/blueprintue/blueprintue-self-hosted-edition/actions/workflows/validate.yml)
[![Codecov](https://img.shields.io/codecov/c/github/blueprintue/blueprintue-self-hosted-edition?logo=codecov)](https://codecov.io/gh/blueprintue/blueprintue-self-hosted-edition)

## Minimum requirements
* \>= PHP 7.4
* \>= MySQL 8 or >= MariaDB 10.6

## How to install?
### Docker Image
| Registry                                                                                                                     | Image                                                 |
|------------------------------------------------------------------------------------------------------------------------------|-------------------------------------------------------|
| [Docker Hub](https://hub.docker.com/r/blueprintue/blueprintue-self-hosted-edition/)                                          | `blueprintue/blueprintue-self-hosted-edition`         |
| [GitHub Container Registry](https://github.com/users/blueprintue/packages/container/package/blueprintue-self-hosted-edition) | `ghcr.io/blueprintue/blueprintue-self-hosted-edition` |

Read [docker-examples](https://github.com/blueprintue/blueprintue-self-hosted-edition/blob/main/docker-examples) about documentation and docker-compose file example.

### FTP / localhost
Download zip file from last [release](https://github.com/blueprintue/blueprintue-self-hosted-edition/releases) or run a `composer install` to have `vendor` folder.  
1. copy folders `app`, `storage`, `vendor` and paste **outside** of the public folder of your server
2. copy folder content `www` and paste **inside** the public folder of your server
3. copy `dump-with-anonymous-user.sql` or `dump-without-anonymous-user.sql` and paste file in your database
4. copy `.env.template` and paste file **outside** of the public folder of your server
5. fill values in `.env.template` file with what you need (database and email)
6. rename `.env.template` to `.env` (see [Configuration explanations](#configuration-explanations))
7. done

The `public folder` means what your http server can show you, usually it is called is `www` or `public_html`

Don't forget to add PHP permissions to read/write in folders `storage`, `www/medias` and where you have `.env` file for cache.  
You can disable cache for `.env` by removing line `$env->enableCache();` in `www/index.php`.

## What's the difference between blueprintUE and blueprintUE self-hosted edition?
blueprintUE self-hosted edition is like blueprintUE but without
* "What is it?" section on the homepage
* blog and tools section
* OAuth for Facebook, Google and Twitter
* metadatas for OpenGraph and Twitter card
* external links in footer
* page "conditions générales d’utilisation"
* blueprintUE logo replaced with an image
* background images and fonts
* OEmbed

## GDPR
Because GDPR you will need to:
* fill contact email [(in .env file)](https://github.com/blueprintue/blueprintue-self-hosted-edition/blob/main/.env.template#L46)
* fill page privacy policy [(in /app/views/www/pages/privacy_policy.php)](https://github.com/blueprintue/blueprintue-self-hosted-edition/blob/main/app/views/www/pages/privacy_policy.php#L25)
* fill page terms of service [(in /app/views/www/pages/terms_of_service.php)](https://github.com/blueprintue/blueprintue-self-hosted-edition/blob/main/app/views/www/pages/terms_of_service.php#L25)

## Configuration explanations
### .env file
#### Database
| Parameter                      | Mandatory | Type   | Default value | Specific values             | Description                                                |
| ------------------------------ | --------- | ------ | ------------- | --------------------------- | ---------------------------------------------------------- |
| DATABASE_DRIVER                | YES       | string |               | mysql \| pgsqlite \| sqlite | database engine used                                       |
| DATABASE_HOST                  | YES       | string |               |                             | host                                                       |
| DATABASE_USER                  | YES       | string |               |                             | user                                                       |
| DATABASE_PASSWORD              | YES       | string |               |                             | password                                                   |
| DATABASE_NAME                  | YES       | string |               |                             | database name                                              |
| DATABASE_PERSISTENT_CONNECTION | NO        | bool   | false         |                             | use persistent connection for database, only for e2e tests |

#### Session
| Parameter                 | Mandatory | Type   | Default value  | Specific values       | Description                                                                     |
| ------------------------- | --------- | ------ | -------------- | --------------------- | ------------------------------------------------------------------------------- |
| SESSION_DRIVER            | NO        | string | default        | default \| database   | session driver used                                                             |
| SESSION_ENCRYPT_KEY       | NO        | string |                |                       | if empty there is no encryption                                                 |
| SESSION_GC_MAXLIFETIME    | NO        | int    | 3600 * 24      |                       | session's lifetime before deletion by garbage collector                         |
| SESSION_LIFETIME          | NO        | int    | 0              |                       | cookie's lifetime for session                                                   |
| SESSION_PATH              | NO        | string | /              |                       | cookie's path for session                                                       |
| SESSION_HTTPS             | NO        | bool   | true           |                       | session cookie will be only accessible on https                                 |
| SESSION_SAMESITE          | NO        | string | Strict         | None \| Lax \| Strict | security policies on how cookies are shared, Lax is mandatory for Twitter OAuth |
| SESSION_REMEMBER_NAME     | NO        | string | remember_token |                       | cookie's name for remember login                                                |
| SESSION_REMEMBER_LIFETIME | NO        | int    | 3600 * 24 * 30 |                       | cookie's lifetime for remember login                                            |
| SESSION_REMEMBER_PATH     | NO        | string | /              |                       | cookie's path for remember login                                                |
| SESSION_REMEMBER_HTTPS    | NO        | bool   | true           |                       | remember cookie will be only accessible on https                                |
| SESSION_REMEMBER_SAMESITE | NO        | string | Strict         | None \| Lax \| Strict | security policies on how cookies are shared, Lax is mandatory for Twitter OAuth |

#### Host
| Parameter | Mandatory | Type   | Default value  | Specific values | Description                                          |
| --------- | --------- | ------ | -------------- | --------------- |------------------------------------------------------|
| HOST      | YES       | string |                |                 | hostname (e.g. blueprintue-self-hosted-edition.test) |
| HTTPS     | YES       | bool   |                |                 | use for detect scheme (http or https)                |

#### Site
| Parameter          | Mandatory | Type   | Default value                   | Specific values | Description                                                                         |
| ------------------ | --------- | ------ |---------------------------------| --------------- |-------------------------------------------------------------------------------------|
| SITE_NAME          | YES       | string | blueprintUE self-hosted edition |                 | name of the site, used for email/description (e.g. blueprintUE self-hosted edition) |
| SITE_BASE_TITLE    | NO        | string |                                 |                 | use for complete the title tag                                                      |
| SITE_DESCRIPTION   | NO        | string |                                 |                 | use for description tag in home page                                                |

#### Anonymous user
| Parameter    | Mandatory | Type   | Default value  | Specific values | Description                                 |
| ------------ |-----------| ------ | -------------- | --------------- | ------------------------------------------- |
| ANONYMOUS_ID | NO        | int    |                |                 | user_id for all anonymous blueprints pasted |

#### Mail
PHPMailer is used as library for sending mails.  
You can use msmtp as service docker for smtp relay and set smtp authentication inside.

| Parameter             | Mandatory | Type   | Default value                                 | Specific values | Description                                                    |
| --------------------- | --------- | ------ |-----------------------------------------------| --------------- |----------------------------------------------------------------|
| MAIL_USE_SMTP         | NO        | bool   | false                                         |                 | set PHPMailer to use SMTP                                      |
| MAIL_SMTP_HOST        | NO        | string | localhost                                     |                 | SMTP host                                                      |
| MAIL_SMTP_PORT        | NO        | int    | 25                                            |                 | SMTP port                                                      |
| MAIL_USE_SMTP_AUTH    | NO        | bool   | false                                         |                 | for SMTP authentication                                        |
| MAIL_SMTP_USER        | NO        | string |                                               |                 | user for SMTP authentication                                   |
| MAIL_SMTP_PASSWORD    | NO        | string |                                               |                 | password for SMTP authentication                               |
| MAIL_FROM_ADDRESS     | YES       | string |                                               |                 | email display for sending emails (register and reset password) |
| MAIL_FROM_NAME        | NO        | string |                                               |                 | name display for sendings emails (register and reset password) |
| MAIL_CONTACT_TO       | YES       | string |                                               |                 | email receiver for the contact page                            |
| MAIL_HEADER_LOGO_PATH | YES       | string | blueprintue-self-hosted-edition_logo-full.png |                 | header image in emails (complete by HOST parameter)            |

## Crons
* GET `/cron/purge_sessions/`: remove old sessions in database (if using sessions database)
* GET `/cron/purge_users_not_confirmed/`: remove users that didn't confirmed their accounts registration after 30 days
* GET `/cron/purge_deleted_blueprints/`: remove expired blueprints
* GET `/cron/set_soft_delete_anonymous_private_blueprints/`: set soft delete for anonymous private blueprints

## FAQ
### How to skip email confirmation?
It's not recommended but this how to skip email confirmation.
In `app/services/www/UserService.php` replace function `generateAndSendConfirmAccountEmail`
```php
    public static function generateAndSendConfirmAccountEmail(int $userID, string $from): bool
    {
        return true;
    }
```
In `app/services/www/UserService.php` replace function `createMemberUser`
```php
    public static function createMemberUser(string $username, string $email, string $password): array
    {
        $errorCode = '#100';

        $userModel = (new UserModel(Application::getDatabase()));
        $userInfosModel = (new UserInfosModel(Application::getDatabase()));

        $forceRollback = false;
        $userID = 0;
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $errorCode = '#200';
            $userID = $userModel->create(
                [
                    'username'     => $username,
                    'slug'         => static::slugify($username),
                    'email'        => $email,
                    'grade'        => 'member',
                    'password'     => $password,
                    'created_at'   => Helper::getNowUTCFormatted(),
                    'confirmed_at' => Helper::getNowUTCFormatted()
                ]
            );

            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because user requirements has been done before
             * For covering we have to test the function outside
             */
            if ($userID === 0) {
                throw new \Exception('User ID is nil');
            }
            // @codeCoverageIgnoreEnd

            $errorCode = '#300';
            $userInfosModel->create(['id_user' => $userID]);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            $forceRollback = true;
            /*
             * In end 2 end testing we can't arrive here because user requirements has been done before
             * For covering we have to test the function outside
             */
            return [null, $errorCode];
            // @codeCoverageIgnoreEnd
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because user requirements has been done before
                 * For covering we have to mock the database
                 */
                Application::getDatabase()->rollbackTransaction();
            // @codeCoverageIgnoreEnd
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return [$userID, null];
    }
```

### How to show PHP errors?
In `www/index.php` file after the `} catch (\Throwable $t) {` you can add this line
```php
$file = $rootDir . $ds . '500-' . gmdate("Y-m-d", time()) . '.txt';
@file_put_contents($file, gmdate("Y-m-d H:i:s", time()) . "\t" . $request->getMethod() . "\t" . $request->getUri() . "\t" . $t->getMessage() . "\n", FILE_APPEND);
```
It will append errors in file to avoid showing it.  
But if you really want to show it instead you can use:
```php
var_dump(gmdate("Y-m-d H:i:s", time()) . "\t" . $request->getMethod() . "\t" . $request->getUri() . "\t" . $t->getMessage() . "\n", FILE_APPEND);
```

## How to dev
### Docker
You have to update your `hosts` file those values
```
# website
127.0.0.1 blueprintue-self-hosted-edition.test

# adminer (database management)
127.0.0.1 adminer.blueprintue-self-hosted-edition.test

# maildev (local smtp)
127.0.0.1 maildev.blueprintue-self-hosted-edition.test
```

All the env variables are in `.dev/docker-compose.yml`.

After you can launch dev environment
```shell
cd .dev
touch .env
docker-compose up -d --build --force-recreate
```

### Neard / Wamp / Old school
You have to update your `hosts` file those values
```
# website
127.0.0.1 blueprintue-self-hosted-edition.test
```

Follow [How to install](#how-to-install).
