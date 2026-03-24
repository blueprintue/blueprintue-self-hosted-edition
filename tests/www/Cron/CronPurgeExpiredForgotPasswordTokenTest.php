<?php

declare(strict_types=1);

namespace tests\www\Cron;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use tests\Common;

/**
 * @internal
 */
class CronPurgeExpiredForgotPasswordTokenTest extends TestCase
{
    use Common;

    /** @throws \Rancoud\Database\DatabaseException */
    public static function setUpBeforeClass(): void
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
    public function testCronPurgeExpiredForgotPasswordTokenGET(): void
    {
        $sql = <<<'SQL'
            REPLACE INTO users (id, password_reset, password_reset_at, username, slug, grade, created_at)
            VALUES (1, 'now + 2h', utc_timestamp() + interval 2 hour, '1', '1', 'member', utc_timestamp()),
                   (2, 'now + 1h', utc_timestamp() + interval 1 hour, '2', '2', 'member', utc_timestamp()),
                   (3, 'now + 30m', utc_timestamp() + interval 30 minute, '3', '3', 'member', utc_timestamp()),
                   (4, 'now', utc_timestamp(), '4', '4', 'member', utc_timestamp()),
                   (5, 'now - 30m', utc_timestamp() - interval 30 minute, '5', '5', 'member', utc_timestamp()),
                   (6, 'now - 1h', utc_timestamp() - interval 1 hour, '6', '6', 'member', utc_timestamp()),
                   (7, 'now - 1h 1s', utc_timestamp() - interval '1:0:1' HOUR_SECOND, '7', '7', 'member', utc_timestamp()),
                   (8, 'now - 2h', utc_timestamp() - interval 2 hour, '8', '8', 'member', utc_timestamp())

        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/purge_expired_forgot_password_token/');

        $users = static::$db->selectAll("SELECT id, password_reset, IF(password_reset_at IS NULL, 'true', 'false') AS password_reset_at_is_null FROM users ORDER BY id ASC");

        $usersExpected = [
            ['id' => 1, 'password_reset' => 'now + 2h', 'password_reset_at_is_null' => 'false'],
            ['id' => 2, 'password_reset' => 'now + 1h', 'password_reset_at_is_null' => 'false'],
            ['id' => 3, 'password_reset' => 'now + 30m', 'password_reset_at_is_null' => 'false'],
            ['id' => 4, 'password_reset' => 'now', 'password_reset_at_is_null' => 'false'],
            ['id' => 5, 'password_reset' => 'now - 30m', 'password_reset_at_is_null' => 'false'],
            ['id' => 6, 'password_reset' => 'now - 1h', 'password_reset_at_is_null' => 'false'],
            ['id' => 7, 'password_reset' => null, 'password_reset_at_is_null' => 'true'],
            ['id' => 8, 'password_reset' => null, 'password_reset_at_is_null' => 'true'],
        ];

        static::assertEqualsCanonicalizing($usersExpected, $users);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testAbortCronPurgeExpiredForgotPasswordTokenGET(): void
    {
        $sql = <<<'SQL'
            REPLACE INTO users (id, password_reset, password_reset_at, username, slug, grade, created_at)
            VALUES (1, 'now + 2h', utc_timestamp() + interval 2 hour, '1', '1', 'member', utc_timestamp()),
                   (2, 'now + 1h', utc_timestamp() + interval 1 hour, '2', '2', 'member', utc_timestamp()),
                   (3, 'now + 30m', utc_timestamp() + interval 30 minute, '3', '3', 'member', utc_timestamp()),
                   (4, 'now', utc_timestamp(), '4', '4', 'member', utc_timestamp()),
                   (5, 'now - 30m', utc_timestamp() - interval 30 minute, '5', '5', 'member', utc_timestamp()),
                   (6, 'now - 1h', utc_timestamp() - interval 1 hour, '6', '6', 'member', utc_timestamp()),
                   (7, 'now - 1h 1s', utc_timestamp() - interval '1:0:1' HOUR_SECOND, '7', '7', 'member', utc_timestamp()),
                   (8, 'now - 2h', utc_timestamp() - interval 2 hour, '8', '8', 'member', utc_timestamp())
        SQL;

        static::$db->exec($sql);

        static::$db->exec('ALTER TABLE users MODIFY password_reset VARCHAR(255) NOT NULL;');

        $this->getResponseFromApplication('GET', '/cron/purge_expired_forgot_password_token/');

        $users = static::$db->selectAll("SELECT id, password_reset, IF(password_reset_at IS NULL, 'true', 'false') AS password_reset_at_is_null FROM users ORDER BY id ASC");

        $usersExpected = [
            ['id' => 1, 'password_reset' => 'now + 2h', 'password_reset_at_is_null' => 'false'],
            ['id' => 2, 'password_reset' => 'now + 1h', 'password_reset_at_is_null' => 'false'],
            ['id' => 3, 'password_reset' => 'now + 30m', 'password_reset_at_is_null' => 'false'],
            ['id' => 4, 'password_reset' => 'now', 'password_reset_at_is_null' => 'false'],
            ['id' => 5, 'password_reset' => 'now - 30m', 'password_reset_at_is_null' => 'false'],
            ['id' => 6, 'password_reset' => 'now - 1h', 'password_reset_at_is_null' => 'false'],
            ['id' => 7, 'password_reset' => 'now - 1h 1s', 'password_reset_at_is_null' => 'false'],
            ['id' => 8, 'password_reset' => 'now - 2h', 'password_reset_at_is_null' => 'false'],
        ];

        static::assertEqualsCanonicalizing($usersExpected, $users);
    }
}
