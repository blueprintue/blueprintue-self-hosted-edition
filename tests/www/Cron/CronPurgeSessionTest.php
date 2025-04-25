<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Cron;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use tests\Common;

class CronPurgeSessionTest extends TestCase
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
    public function testCronPurgeSessionGET(): void
    {
        $sql = <<<'SQL'
            INSERT INTO sessions (id, last_access, content)
            VALUES ('now + 30h', utc_timestamp() + interval 30 hour, '1'),
                   ('now + 1h', utc_timestamp() + interval 1 hour, '2'),
                   ('now', utc_timestamp(), '3'),
                   ('now - 1h', utc_timestamp() - interval 1 hour, '4'),
                   ('now - 5h', utc_timestamp() - interval 5 hour, '5'),
                   ('now - 10h', utc_timestamp() - interval 10 hour, '6'),
                   ('now - 15h', utc_timestamp() - interval 15 hour, '7'),
                   ('now - 20h', utc_timestamp() - interval 20 hour, '8'),
                   ('now - 25h', utc_timestamp() - interval 25 hour, '9'),
                   ('now - 30h', utc_timestamp() - interval 30 hour, '10')
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/purge_sessions/');

        $sessions = static::$db->selectCol('SELECT id FROM sessions ORDER BY content');
        $sessionsLeft = [
            'now + 30h',
            'now + 1h',
            'now',
            'now - 1h',
            'now - 5h',
            'now - 10h',
            'now - 15h',
            'now - 20h',
        ];
        static::assertEqualsCanonicalizing($sessionsLeft, $sessions);
    }
}
