<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Home;

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

class HomeLastBlueprintsTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
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
     * @throws SecurityException
     *
     * @return array[]
     */
    public static function dataCasesLastBlueprints(): array
    {
        return [
            'no blueprints - nothing in database' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints'
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            'no blueprints - no published_at' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), null, 'public')",
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            'no blueprints - public but expiration passed' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, expiration) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', '2020-01-01 01:01:01')",
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            'no blueprints - deleted' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            'no blueprints - exposure private' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            'no blueprints - exposure unlisted' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'hasHeader' => false,
                'content'   => '<p>No blueprints for the moment</p>'
            ],
            '1 blueprint' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'anonymous', NULL, 'anonymous', 'anonymous@mail', utc_timestamp())",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (' . static::$anonymousID . ", 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp())",
                ],
                'hasHeader' => true,
                'content'   => static::getHTMLItemBlueprint('slug', 'blueprint', '4.0', 'title', 'anonymous', 'anonymous', 'few seconds ago', null),
            ],
            '5 blueprints max - 10 blueprints in database' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'anonymous', NULL, 'anonymous', 'anonymous@mail', utc_timestamp())",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 1', 'file 1', 'title 1', 1, utc_timestamp(), utc_timestamp() - interval 7 month, '4.20', 'blueprint')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 2', 'file 2', 'title 2', 1, utc_timestamp(), utc_timestamp() - interval 2 month, '4.19', 'metasound')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 3', 'file 3', 'title 3', 1, utc_timestamp(), utc_timestamp() - interval 8 month, '4.18', 'animation')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 4', 'file 4', 'title 4', 1, utc_timestamp(), utc_timestamp() - interval 10 month, '4.17', 'blueprint')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 5', 'file 5', 'title 5', 1, utc_timestamp(), utc_timestamp() - interval 9 month, '4.16', 'material')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 6', 'file 6', 'title 6', 1, utc_timestamp(), utc_timestamp() - interval 1 month, '4.15', 'animation')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 7', 'file 7', 'title 7', 1, utc_timestamp(), utc_timestamp() - interval 6 month, '4.14', 'blueprint')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 8', 'file 8', 'title 8', 1, utc_timestamp(), utc_timestamp() - interval 3 month, '4.13', 'material')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 9', 'file 9', 'title 9', 1, utc_timestamp(), utc_timestamp() - interval 5 month, '4.12', 'niagara')",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, type) VALUES (' . static::$anonymousID . ", 'slug 10', 'file 10', 'title 10', 1, utc_timestamp(), utc_timestamp() - interval 4 month, '4.4', 'behavior_tree')",
                ],
                'hasHeader' => true,
                'content'   => \implode(
                    "\n",
                    [
                        static::getHTMLItemBlueprint('slug 6', 'animation', '4.15', 'title 6', 'anonymous', 'anonymous', '1 months ago', null),
                        static::getHTMLItemBlueprint('slug 2', 'metasound', '4.19', 'title 2', 'anonymous', 'anonymous', '2 months ago', null),
                        static::getHTMLItemBlueprint('slug 8', 'material', '4.13', 'title 8', 'anonymous', 'anonymous', '3 months ago', null),
                        static::getHTMLItemBlueprint('slug 10', 'behavior_tree', '4.4', 'title 10', 'anonymous', 'anonymous', '4 months ago', null),
                        static::getHTMLItemBlueprint('slug 9', 'niagara', '4.12', 'title 9', 'anonymous', 'anonymous', '5 months ago', null),
                    ]
                )
            ],
            'xss' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, '<script>alert(\"user_name\")</script>', NULL, '<script>alert(\"user_slug\")</script>', 'anonymous@mail', utc_timestamp())",
                    'INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, ue_version, thumbnail) VALUES (' . static::$anonymousID . ", '<script>alert(\"slug\")</script>', '<script>alert(\"file\")</script>', '<script>alert(\"title\")</script>', 4, utc_timestamp(), utc_timestamp(), '<>4</', '<script>alert(\"thumbnail\")</script>')",
                ],
                'hasHeader' => true,
                'content'   => static::getHTMLItemBlueprint('<script>alert("slug")</script>', 'blueprint', '<>4</', '<script>alert("title")</script>', '<script>alert("user_name")</script>', '<script>alert("user_slug")</script>', 'few seconds ago', '<script>alert("thumbnail")</script>'),
            ],
        ];
    }

    /**
     * @dataProvider dataCasesLastBlueprints
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('dataCasesLastBlueprints')]
    public function testHomeGETLastBlueprints(array $sqlQueries, bool $hasHeader, string $content): void
    {
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        $response = $this->getResponseFromApplication('GET', '/');

        if ($hasHeader) {
            $this->doTestHtmlMain($response, $this->getHTMLHeaderListBlueprints());
        } else {
            $this->doTestHtmlMainNot($response, $this->getHTMLHeaderListBlueprints());
        }

        $this->doTestHtmlMain($response, $content);
    }

    protected function getHTMLHeaderListBlueprints(): string
    {
        /* @noinspection HtmlUnknownTarget */
        return <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element block__element--home">
<h2 class="block__title">Last public pasted <span class="block__title--emphasis">blueprints</span></h2>
<div>
<a class="block__link block__link--home" href="/last-blueprints/">Last blueprints</a>
<a class="block__link block__link--home" href="/search/">Advanced Search</a>
<a class="block__link block__link--home" href="/most-discussed-blueprints/">Most discussed</a>
<a class="block__link block__link--home" href="/type/material/">Material blueprint</a>
<a class="block__link block__link--home-last" href="/tags/">Tags</a>
</div>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
HTML;
    }

    /**
     * @throws SecurityException
     */
    protected static function getHTMLItemBlueprint(string $blueprintSlug, string $type, string $version, string $title, string $author, string $profileSlug, string $date, ?string $thumbnail): string
    {
        $blueprintURL = Security::escAttr('/blueprint/' . $blueprintSlug . '/');
        $profileURL = Security::escAttr('/profile/' . $profileSlug . '/');
        $image = <<<HTML
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
HTML;

        if ($thumbnail !== null) {
            $thumbnailURL = Security::escAttr('/medias/blueprints/' . $thumbnail);
            $image = <<<HTML
<img alt="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder" src="$thumbnailURL" />
HTML;
        }

        $type = Security::escHTML($type);
        if ($type === 'behavior_tree') {
            $type = 'behavior<br/>tree';
        }
        $version = Security::escHTML($version);
        $title = Security::escHTML($title);
        $author = Security::escHTML($author);
        $date = Security::escHTML($date);

        return <<<HTML
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="$blueprintURL">
$image
</a>
</div>
<div class="list__col" data-name="Type">$type</div>
<div class="list__col" data-name="UE Version">$version</div>
<div class="list__col" data-name="Title"><a class="list__link" href="$blueprintURL">$title</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="$profileURL">$author</a></div>
<div class="list__col" data-name="Date">$date</div>
</li>
HTML;
    }
}
