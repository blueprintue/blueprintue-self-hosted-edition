<?php

declare(strict_types=1);

namespace tests\www\Cron;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use tests\Common;

/** @internal */
class CronPurgeUsersNotConfirmedTest extends TestCase
{
    use Common;

    /** @throws \Rancoud\Database\DatabaseException */
    protected function setUp(): void
    {
        static::setDatabaseEmptyStructure();
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testCronPurgeUsersNotConfirmedGET(): void
    {
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `password_reset`, `password_reset_at`, `grade`, `avatar`, `remember_token`, `created_at`, `confirmed_token`, `confirmed_sent_at`, `confirmed_at`, `last_login_at`)
            VALUES (1, 'user_01', NULL, 'user_01', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, utc_timestamp(), NULL),
                   (2, 'user_02', NULL, 'user_02', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, NULL, NULL),
                   (3, 'user_03', NULL, 'user_03', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, utc_timestamp(), NULL),
                   (4, 'user_04', NULL, 'user_04', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, NULL, NULL),
                   (5, 'user_05', NULL, 'user_05', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, utc_timestamp(), NULL),
                   (6, 'user_06', NULL, 'user_06', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, NULL, NULL),
                   (7, 'user_07', NULL, 'user_07', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, utc_timestamp(), NULL),
                   (8, 'user_08', NULL, 'user_08', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, NULL, NULL),
                   (9, 'user_09', NULL, 'user_09', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp(), NULL, NULL, utc_timestamp(), NULL),
                   (10,'user_10', NULL, 'user_10', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp(), NULL, NULL, NULL, NULL);
        SQL;

        static::$db->exec($sql);

        $sql = <<<'SQL'
            INSERT INTO `users_infos` (`id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment`)
            VALUES (1, 0, 0, 0, 0),
                   (2, 0, 0, 0, 0),
                   (3, 0, 0, 0, 0),
                   (4, 0, 0, 0, 0),
                   (5, 0, 0, 0, 0),
                   (6, 0, 0, 0, 0),
                   (7, 0, 0, 0, 0),
                   (8, 0, 0, 0, 0),
                   (9, 0, 0, 0, 0),
                   (10, 0, 0, 0, 0);
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/purge_users_not_confirmed/');

        $users = static::$db->selectCol('SELECT id FROM users ORDER BY id ASC');
        $usersLeft = [
            1,
            3,
            5,
            6,
            7,
            8,
            9,
            10
        ];
        static::assertEqualsCanonicalizing($usersLeft, $users);

        $usersInfos = static::$db->selectCol('SELECT id_user FROM users_infos ORDER BY id_user ASC');
        static::assertEqualsCanonicalizing($usersLeft, $usersInfos);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testAbortCronPurgeUsersNotConfirmedGET(): void
    {
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `password_reset`, `password_reset_at`, `grade`, `avatar`, `remember_token`, `created_at`, `confirmed_token`, `confirmed_sent_at`, `confirmed_at`, `last_login_at`)
            VALUES (1, 'user_01', NULL, 'user_01', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, utc_timestamp(), NULL),
                   (2, 'user_02', NULL, 'user_02', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, NULL, NULL),
                   (3, 'user_03', NULL, 'user_03', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, utc_timestamp(), NULL),
                   (4, 'user_04', NULL, 'user_04', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 60 day, NULL, NULL, NULL, NULL),
                   (5, 'user_05', NULL, 'user_05', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, utc_timestamp(), NULL),
                   (6, 'user_06', NULL, 'user_06', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, NULL, NULL),
                   (7, 'user_07', NULL, 'user_07', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, utc_timestamp(), NULL),
                   (8, 'user_08', NULL, 'user_08', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp() - interval 15 day, NULL, NULL, NULL, NULL),
                   (9, 'user_09', NULL, 'user_09', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp(), NULL, NULL, utc_timestamp(), NULL),
                   (10,'user_10', NULL, 'user_10', NULL, NULL, NULL, 'member', NULL, NULL, utc_timestamp(), NULL, NULL, NULL, NULL);
        SQL;

        static::$db->exec($sql);

        $sql = <<<'SQL'
            ALTER TABLE `users_infos` DROP `id_user`;
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/purge_users_not_confirmed/');

        $users = static::$db->selectCol('SELECT id FROM users ORDER BY id ASC');
        $usersLeft = [
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            10
        ];

        static::assertEqualsCanonicalizing($usersLeft, $users);
    }
}
