<?php

/* @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class BlueprintGETVideoBlueprintTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::addUsers();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing multiple videos.
     *
     * @return array[]
     */
    public static function provideBlueprintGETVideoBlueprintDataCases(): iterable
    {
        yield 'no video' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => null,
        ];

        yield 'youtube' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'youtube', 'youtube')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'youtube',
                'provider'    => 'youtube',
                'privacy_url' => 'https://policies.google.com/privacy'
            ],
        ];

        yield 'dailymotion' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'dailymotion', 'dailymotion')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'dailymotion',
                'provider'    => 'dailymotion',
                'privacy_url' => 'https://www.dailymotion.com/legal/privacy'
            ],
        ];

        yield 'vimeo' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'vimeo', 'vimeo')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'vimeo',
                'provider'    => 'vimeo',
                'privacy_url' => 'https://vimeo.com/privacy'
            ],
        ];

        yield 'niconico' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'niconico', 'niconico')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'niconico',
                'provider'    => 'niconico',
                'privacy_url' => 'https://account.nicovideo.jp/rules/account'
            ],
        ];

        yield 'bilibili' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'bilibili', 'bilibili')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'bilibili',
                'provider'    => 'bilibili',
                'privacy_url' => 'https://www.bilibili.com/blackboard/privacy-pc.html'
            ],
        ];

        yield 'unsupported' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, video, video_provider) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 'unsupported', 'unsupported')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'  => 'slug_public',
            'video' => [
                'url'         => 'unsupported',
                'provider'    => 'unsupported',
                'privacy_url' => '#'
            ],
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('provideBlueprintGETVideoBlueprintDataCases')]
    public function testBlueprintGETVideoBlueprint(array $sqlQueries, string $slug, ?array $video): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // video
        if ($video !== null) {
            $this->doTestHtmlMain($response, '<div class="blueprint__video"');
            $this->doTestHtmlMain($response, 'data-video-iframe-url="' . Security::escAttr($video['url']) . '">');
            $this->doTestHtmlMain($response, 'This video is provided by ' . Security::escHTML($video['provider']) . ' using cookies.');
            $this->doTestHtmlMain($response, '<a class="blueprint__video--policy" href="' . Security::escAttr($video['privacy_url']) . '" rel="noopener noreferrer nofollow" target="_blank">See their privacy page.</a>');
            $this->doTestHtmlMain($response, '<button class="form__button form__button--primary" id="blueprint-video-button-provider">Accept ' . Security::escHTML($video['provider']) . ' cookies</button>');
        } else {
            $this->doTestHtmlMainNot($response, '<div class="blueprint__video"');
        }
    }
}
