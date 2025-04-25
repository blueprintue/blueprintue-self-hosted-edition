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

/** @internal */
class CronPurgeDeletedBlueprintsTest extends TestCase
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
    public function testCronPurgeDeletedBlueprintsGET(): void
    {
        $storageFolder = \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR . 'storage_test';

        $sqls = [
            <<<'SQL'
                INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `password_reset`, `password_reset_at`, `grade`, `avatar`, `remember_token`, `created_at`, `confirmed_token`, `confirmed_sent_at`, `confirmed_at`, `last_login_at`)
                VALUES (101, 'user_101', NULL, 'user_101', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (102, 'user_102', NULL, 'user_102', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (103, 'user_103', NULL, 'user_103', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (104, 'user_104', NULL, 'user_104', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (105, 'user_105', NULL, 'user_105', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (106, 'user_106', NULL, 'user_106', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (107, 'user_107', NULL, 'user_107', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (108, 'user_108', NULL, 'user_108', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (109, 'user_109', NULL, 'user_109', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (110, 'user_110', NULL, 'user_110', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL);
            SQL,
            <<<'SQL'
                INSERT INTO `users_infos` (`id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment`)
                VALUES (101,  5,  6,  2, 3),
                       (102,  7, 60, 32, 0),
                       (103,  9, 80, 15, 0),
                       (104, 10, 15,  0, 9),
                       (105, 23, 66,  0, 4),
                       (106, 15, 44, 14, 5),
                       (107, 65,  4, 41, 1),
                       (108, 99,  0,  2, 2),
                       (109,  0, 10,  3, 0),
                       (110,  0,  1,  4, 0);
            SQL,
            <<<'SQL'
                INSERT INTO `blueprints` (`id`, `id_author`, `slug`, `file_id`, `title`, `type`, `ue_version`, `current_version`, `thumbnail`, `created_at`, `published_at`, `exposure`, `deleted_at`, `expiration`)
                VALUES (201, 101, 'bp_01', 'azerty01', 'title_01', 'blueprint', '5.1', '2', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (202, 101, 'bp_02', 'azerty02', 'title_02', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, '2000-01-01 00:00:00'),
                       (203, 102, 'bp_03', 'azerty03', 'title_03', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (204, 102, 'bp_04', 'azerty04', 'title_04', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  '2000-01-01 00:00:00', NULL),
                       (205, 106, 'bp_05', 'azerty05', 'title_05', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (206, 103, 'bp_06', 'azerty06', 'title_06', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, '2000-01-01 00:00:00'),
                       (207, 104, 'bp_07', 'azerty07', 'title_07', 'blueprint', '5.1', '2', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'unlisted', '2000-01-01 00:00:00', NULL),
                       (208, 109, 'bp_08', 'azerty08', 'title_08', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   '2000-01-01 00:00:00', NULL),
                       (209, 105, 'bp_09', 'azerty09', 'title_08', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (210, 110, 'bp_10', 'azerty10', 'title_10', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, '2000-01-01 00:00:00'),
                       (211, 101, 'bp_11', 'azerty11', 'title_11', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, UTC_TIMESTAMP() + interval 60 day),
                       (212, 102, 'bp_12', 'azerty12', 'title_12', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, UTC_TIMESTAMP() + interval 60 day);
            SQL,
            <<<'SQL'
                INSERT INTO `blueprints_version` (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES
                (700, 201, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (701, 201, '2', 'Second commit', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (702, 202, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (703, 203, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (704, 204, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (705, 205, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (706, 206, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (707, 207, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (708, 208, '1', 'Second commit', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (709, 208, '2', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (710, 209, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (711, 210, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (712, 211, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (713, 212, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (714, 251, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (715, 261, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP())
            SQL,
            <<<'SQL'
                INSERT INTO `comments` (`id`, `id_author`, `id_blueprint`, `name_fallback`, `content`, `created_at`)
                VALUES (301, 110, 201, NULL, 'a', UTC_TIMESTAMP()),
                       (302, 110, 202, NULL, 'b', UTC_TIMESTAMP()),
                       (303, 103, 204, NULL, 'c', UTC_TIMESTAMP()),
                       (304, 104,   2, NULL, 'd', UTC_TIMESTAMP()),
                       (305, 104,   3, NULL, 'e', UTC_TIMESTAMP()),
                       (306, 101,   4, NULL, 'f', UTC_TIMESTAMP()),
                       (307, 109,   9, NULL, 'g', UTC_TIMESTAMP()),
                       (308, 106, 206, NULL, 'h', UTC_TIMESTAMP()),
                       (309, 106,   1, NULL, 'i', UTC_TIMESTAMP()),
                       (310, 106, 210, NULL, 'j', UTC_TIMESTAMP()),
                       (311, 101, 210, NULL, 'k', UTC_TIMESTAMP()),
                       (312, 104, 211, NULL, 'l', UTC_TIMESTAMP())
            SQL
        ];

        // database
        foreach ($sqls as $sql) {
            static::$db->exec($sql);
        }

        // files
        $dirs = [
            [
                'fileID'       => 'azerty01',
                'still_exists' => true
            ],
            [
                'fileID'       => 'azerty02',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty03',
                'still_exists' => true
            ],
            [
                'fileID'       => 'azerty04',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty05',
                'still_exists' => true
            ],
            [
                'fileID'       => 'azerty06',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty07',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty08',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty09',
                'still_exists' => true
            ],
            [
                'fileID'       => 'azerty10',
                'still_exists' => false
            ],
            [
                'fileID'       => 'azerty11',
                'still_exists' => true
            ],
            [
                'fileID'       => 'azerty12',
                'still_exists' => true
            ]
        ];

        foreach ($dirs as $dir) {
            $caracters = \mb_str_split($dir['fileID']);
            $subfolder = '';
            foreach ($caracters as $c) {
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }

            if (!\is_dir($storageFolder . \DIRECTORY_SEPARATOR . $subfolder)) {
                \mkdir($storageFolder . \DIRECTORY_SEPARATOR . $subfolder, 0777, true);
            }

            $fullpath = $storageFolder . \DIRECTORY_SEPARATOR . $subfolder . $dir['fileID'] . '-1.txt';
            \file_put_contents($fullpath, 'test');

            $fullpath = $storageFolder . \DIRECTORY_SEPARATOR . $subfolder . $dir['fileID'] . '-2.txt';
            \file_put_contents($fullpath, 'test');
        }

        // launch cron
        $this->getResponseFromApplication('GET', '/cron/purge_deleted_blueprints/');

        // check database
        $usersInfos = static::$db->selectAll('SELECT `id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment` FROM users_infos ORDER BY id_user ASC');
        $usersInfosExpected = [
            ['id_user' => '101', 'count_public_blueprint' =>  '1', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '2', 'count_private_comment' => '0'],
            ['id_user' => '102', 'count_public_blueprint' =>  '2', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '2', 'count_private_comment' => '0'],
            ['id_user' => '103', 'count_public_blueprint' =>  '0', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '0', 'count_private_comment' => '0'],
            ['id_user' => '104', 'count_public_blueprint' =>  '0', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '0', 'count_private_comment' => '1'],
            ['id_user' => '105', 'count_public_blueprint' => '23', 'count_public_comment' => '66', 'count_private_blueprint' =>  '0', 'count_private_comment' => '4'],
            ['id_user' => '106', 'count_public_blueprint' =>  '1', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '1', 'count_private_comment' => '0'],
            ['id_user' => '107', 'count_public_blueprint' => '65', 'count_public_comment' =>  '4', 'count_private_blueprint' => '41', 'count_private_comment' => '1'],
            ['id_user' => '108', 'count_public_blueprint' => '99', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '2', 'count_private_comment' => '2'],
            ['id_user' => '109', 'count_public_blueprint' =>  '0', 'count_public_comment' =>  '0', 'count_private_blueprint' =>  '0', 'count_private_comment' => '0'],
            ['id_user' => '110', 'count_public_blueprint' =>  '0', 'count_public_comment' =>  '1', 'count_private_blueprint' =>  '0', 'count_private_comment' => '1']
        ];

        if (\PHP_MAJOR_VERSION >= 8 && \PHP_MINOR_VERSION >= 1) {
            foreach ($usersInfos as $key => $value) {
                $usersInfos[$key]['id_user'] = (string) $value['id_user'];
                $usersInfos[$key]['count_public_blueprint'] = (string) $value['count_public_blueprint'];
                $usersInfos[$key]['count_public_comment'] = (string) $value['count_public_comment'];
                $usersInfos[$key]['count_private_blueprint'] = (string) $value['count_private_blueprint'];
                $usersInfos[$key]['count_private_comment'] = (string) $value['count_private_comment'];
            }
        }

        static::assertSame($usersInfosExpected, $usersInfos);

        $comments = static::$db->selectCol('SELECT `id` FROM comments ORDER BY id ASC');
        $commentsExpected = [
            '301',
            '304',
            '305',
            '306',
            '307',
            '309',
            '312'
        ];
        static::assertEqualsCanonicalizing($commentsExpected, $comments);

        $blueprints = static::$db->selectCol('SELECT `id` FROM blueprints ORDER BY id ASC');
        $blueprintsExpected = [
            '201',
            '203',
            '205',
            '209',
            '211',
            '212'
        ];
        static::assertEqualsCanonicalizing($blueprintsExpected, $blueprints);

        $blueprintsVersion = static::$db->selectCol('SELECT `id` FROM blueprints_version ORDER BY id_blueprint ASC');
        $blueprintsVersionExpected = [
            '700',
            '701',
            '703',
            '705',
            '710',
            '712',
            '713',
            '714',
            '715'
        ];
        static::assertEqualsCanonicalizing($blueprintsVersionExpected, $blueprintsVersion);

        // check files
        foreach ($dirs as $dir) {
            $caracters = \mb_str_split($dir['fileID']);
            $subfolder = '';
            foreach ($caracters as $c) {
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }

            $countFiles = \count(\glob($storageFolder . \DIRECTORY_SEPARATOR . $subfolder . $dir['fileID'] . '-*.txt'));
            if ($dir['still_exists'] === true) {
                static::assertTrue($countFiles > 0, 'still_exists=true failed for ' . $dir['fileID']);
            } else {
                static::assertSame(0, $countFiles, 'still_exists=false failed for ' . $dir['fileID']);
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testAbortCronPurgeDeletedBlueprintsGET(): void
    {
        $sqls = [
            <<<'SQL'
                INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `password_reset`, `password_reset_at`, `grade`, `avatar`, `remember_token`, `created_at`, `confirmed_token`, `confirmed_sent_at`, `confirmed_at`, `last_login_at`)
                VALUES (101, 'user_101', NULL, 'user_101', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (102, 'user_102', NULL, 'user_102', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (103, 'user_103', NULL, 'user_103', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (104, 'user_104', NULL, 'user_104', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (105, 'user_105', NULL, 'user_105', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (106, 'user_106', NULL, 'user_106', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (107, 'user_107', NULL, 'user_107', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (108, 'user_108', NULL, 'user_108', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (109, 'user_109', NULL, 'user_109', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL),
                       (110, 'user_110', NULL, 'user_110', NULL, NULL, NULL, 'member', NULL, NULL, UTC_TIMESTAMP(), NULL, NULL, UTC_TIMESTAMP(), NULL);
            SQL,
            <<<'SQL'
                INSERT INTO `users_infos` (`id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment`)
                VALUES (101,  5,  6,  2, 3),
                       (102,  7, 60, 32, 0),
                       (103,  9, 80, 15, 0),
                       (104, 10, 15,  0, 9),
                       (105, 23, 66,  0, 4),
                       (106, 15, 44, 14, 5),
                       (107, 65,  4, 41, 1),
                       (108, 99,  0,  2, 2),
                       (109,  0, 10,  3, 0),
                       (110,  0,  1,  4, 0);
            SQL,
            <<<'SQL'
                INSERT INTO `blueprints` (`id`, `id_author`, `slug`, `file_id`, `title`, `type`, `ue_version`, `current_version`, `thumbnail`, `created_at`, `published_at`, `exposure`, `deleted_at`, `expiration`)
                VALUES (201, 101, 'bp_01', 'azerty01', 'title_01', 'blueprint', '5.1', '2', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (202, 101, 'bp_02', 'azerty02', 'title_02', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, '2000-01-01 00:00:00'),
                       (203, 102, 'bp_03', 'azerty03', 'title_03', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (204, 102, 'bp_04', 'azerty04', 'title_04', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  '2000-01-01 00:00:00', NULL),
                       (205, 106, 'bp_05', 'azerty05', 'title_05', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (206, 103, 'bp_06', 'azerty06', 'title_06', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, '2000-01-01 00:00:00'),
                       (207, 104, 'bp_07', 'azerty07', 'title_07', 'blueprint', '5.1', '2', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'unlisted', '2000-01-01 00:00:00', NULL),
                       (208, 109, 'bp_08', 'azerty08', 'title_08', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   '2000-01-01 00:00:00', NULL),
                       (209, 105, 'bp_09', 'azerty09', 'title_08', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, NULL),
                       (210, 110, 'bp_10', 'azerty10', 'title_10', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, '2000-01-01 00:00:00'),
                       (211, 101, 'bp_11', 'azerty11', 'title_11', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'private',  NULL, UTC_TIMESTAMP() + interval 60 day),
                       (212, 102, 'bp_12', 'azerty12', 'title_12', 'blueprint', '5.1', '1', NULL, UTC_TIMESTAMP(), UTC_TIMESTAMP(), 'public',   NULL, UTC_TIMESTAMP() + interval 60 day);
            SQL,
            <<<'SQL'
                INSERT INTO `blueprints_version` (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES
                (700, 201, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (701, 201, '2', 'Second commit', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (702, 202, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (703, 203, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (704, 204, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (705, 205, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (706, 206, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (707, 207, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (708, 208, '1', 'Second commit', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (709, 208, '2', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (710, 209, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (711, 210, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (712, 211, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (713, 212, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (714, 251, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP()),
                (715, 261, '1', 'First commit',  UTC_TIMESTAMP(), UTC_TIMESTAMP())
            SQL,
            <<<'SQL'
                INSERT INTO `comments` (`id`, `id_author`, `id_blueprint`, `name_fallback`, `content`, `created_at`)
                VALUES (301, 110, 201, NULL, 'a', UTC_TIMESTAMP()),
                       (302, 110, 202, NULL, 'b', UTC_TIMESTAMP()),
                       (303, 103, 204, NULL, 'c', UTC_TIMESTAMP()),
                       (304, 104,   2, NULL, 'd', UTC_TIMESTAMP()),
                       (305, 104,   3, NULL, 'e', UTC_TIMESTAMP()),
                       (306, 101,   4, NULL, 'f', UTC_TIMESTAMP()),
                       (307, 109,   9, NULL, 'g', UTC_TIMESTAMP()),
                       (308, 106, 206, NULL, 'h', UTC_TIMESTAMP()),
                       (309, 106,   1, NULL, 'i', UTC_TIMESTAMP()),
                       (310, 106, 210, NULL, 'j', UTC_TIMESTAMP()),
                       (311, 101, 210, NULL, 'k', UTC_TIMESTAMP()),
                       (312, 104, 211, NULL, 'l', UTC_TIMESTAMP())
            SQL
        ];

        // database
        foreach ($sqls as $sql) {
            static::$db->exec($sql);
        }

        static::$db->dropTables('blueprints');

        $usersInfosExpected = static::$db->selectAll('SELECT `id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment` FROM users_infos ORDER BY id_user ASC');

        // launch cron
        $this->getResponseFromApplication('GET', '/cron/purge_deleted_blueprints/');

        // check database
        $usersInfos = static::$db->selectAll('SELECT `id_user`, `count_public_blueprint`, `count_public_comment`, `count_private_blueprint`, `count_private_comment` FROM users_infos ORDER BY id_user ASC');

        static::assertSame($usersInfosExpected, $usersInfos);
    }
}
