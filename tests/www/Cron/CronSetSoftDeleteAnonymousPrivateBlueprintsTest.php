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

class CronSetSoftDeleteAnonymousPrivateBlueprintsTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    protected function setUp(): void
    {
        static::setDatabaseEmptyStructure();
        static::addUsers();
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    public function testCronSetSoftDeleteAnonymousPrivateBlueprintsGET(): void
    {
        $sql = <<<SQL
            INSERT INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure)
            VALUES
                (101, 2, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (102, 2, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (103, 2, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private'),
                (104, 1, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (105, 1, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (106, 1, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp(), utc_timestamp(), 'private')
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/set_soft_delete_anonymous_private_blueprints/');

        $actualBlueprintsSoftDeleted = static::$db->selectCol('SELECT id FROM blueprints WHERE deleted_at IS NOT NULL');
        $expectedBlueprintsSoftDeleted = [
            103
        ];
        static::assertEqualsCanonicalizing($expectedBlueprintsSoftDeleted, $actualBlueprintsSoftDeleted);
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    public function testAbortCronSetSoftDeleteAnonymousPrivateBlueprintsGET(): void
    {
        $sql = <<<SQL
            INSERT INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure)
            VALUES
                (101, 2, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (102, 2, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (103, 2, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private'),
                (104, 1, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (105, 1, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (106, 1, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp(), utc_timestamp(), 'private')
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/set_soft_delete_anonymous_private_blueprints/', [], [], [], [], [], [], [], 'tests-no-anonymous-user.env');

        $actualBlueprintsSoftDeleted = static::$db->selectCol('SELECT id FROM blueprints WHERE deleted_at IS NOT NULL');
        $expectedBlueprintsSoftDeleted = [];
        static::assertEqualsCanonicalizing($expectedBlueprintsSoftDeleted, $actualBlueprintsSoftDeleted);
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    public function testAbortErrorCronSetSoftDeleteAnonymousPrivateBlueprintsGET(): void
    {
        $sql = <<<SQL
            INSERT INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure)
            VALUES
                (101, 2, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (102, 2, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (103, 2, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private'),
                (104, 1, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp(), utc_timestamp(), 'public'),
                (105, 1, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                (106, 1, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp(), utc_timestamp(), 'private')
        SQL;

        static::$db->exec($sql);

        $sql = <<<SQL
            ALTER TABLE `blueprints` DROP `exposure`
        SQL;

        static::$db->exec($sql);

        $this->getResponseFromApplication('GET', '/cron/set_soft_delete_anonymous_private_blueprints/');

        $actualBlueprintsSoftDeleted = static::$db->selectCol('SELECT id FROM blueprints WHERE deleted_at IS NOT NULL');
        $expectedBlueprintsSoftDeleted = [];
        static::assertEqualsCanonicalizing($expectedBlueprintsSoftDeleted, $actualBlueprintsSoftDeleted);
    }
}
