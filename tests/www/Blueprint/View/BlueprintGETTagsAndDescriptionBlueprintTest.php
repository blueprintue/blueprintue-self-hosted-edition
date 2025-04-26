<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpTooManyParametersInspection */

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
class BlueprintGETTagsAndDescriptionBlueprintTest extends TestCase
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
     * Use for testing informations about blueprint tags, description.
     *
     * @return array[]
     */
    public static function provideBlueprintGETTagsAndDescriptionBlueprintDataCases(): iterable
    {
        yield 'no tag / no description' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'        => 'slug_public',
            'tags'        => null,
            'description' => null,
        ];

        yield 'no tag (tag is invalid) / no description' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, tags) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', '999')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'        => 'slug_public',
            'tags'        => null,
            'description' => null,
        ];

        yield '1 tag / has description' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, tags, description) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', '55', 'descr<script>alert(1)</script>iption')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (1, 'mem<script>alert(1)</script>ber', null, 'mem\"><script>alert(1)</script>ber', 'member@mail', utc_timestamp(), 'ava\"><script>alert(1)</script>tar.jpg')",
                "REPLACE INTO tags (id, name, slug) VALUES (55, '<script>alert(1)</script>', '\"><script>alert(1)</script>')",
            ],
            'slug' => 'slug_public',
            'tags' => [
                [
                    'name' => '<script>alert(1)</script>',
                    'slug' => '"><script>alert(1)</script>',
                ]
            ],
            'description' => 'descr<script>alert(1)</script>iption',
        ];

        yield '3 tags / has description' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, tags, description) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', '55,66,77', 'descr<script>alert(1)</script>iption')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (1, 'mem<script>alert(1)</script>ber', null, 'mem\"><script>alert(1)</script>ber', 'member@mail', utc_timestamp(), 'ava\"><script>alert(1)</script>tar.jpg')",
                "REPLACE INTO tags (id, name, slug) VALUES (55, 'my tag 1', 'my_tag_1'), (66, 'my tag 2', 'my_tag_2'), (77, 'my tag 3', 'my_tag_3')",
            ],
            'slug' => 'slug_public',
            'tags' => [
                [
                    'name' => 'my tag 1',
                    'slug' => 'my_tag_1',
                ],
                [
                    'name' => 'my tag 2',
                    'slug' => 'my_tag_2',
                ],
                [
                    'name' => 'my tag 3',
                    'slug' => 'my_tag_3',
                ]
            ],
            'description' => 'descr<script>alert(1)</script>iption',
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('provideBlueprintGETTagsAndDescriptionBlueprintDataCases')]
    public function testBlueprintGETTagsAndDescriptionBlueprint(array $sqlQueries, string $slug, ?array $tags, ?string $description): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // tags
        if ($tags !== null) {
            $this->doTestHtmlMain($response, '<ul class="tag__items">');
            foreach ($tags as $tag) {
                $this->doTestHtmlMain($response, '<li class="tag__item"><a class="block__link block__link--no-margin" href="' . Security::escAttr('/tag/' . $tag['slug'] . '/1/') . '">' . Security::escHTML($tag['name']) . '</a></li>');
            }
        } else {
            $this->doTestHtmlMainNot($response, '<ul class="tag__items">');
        }

        // description
        if ($description !== null) {
            $parsedown = (new \Parsedown())->setSafeMode(true);
            $this->doTestHtmlMain($response, '<div class="blueprint__description block__markdown">' . "\n" . $parsedown->text($description) . '                </div>');
        } else {
            $this->doTestHtmlMainNot($response, '<div class="blueprint__description block__markdown">');
        }
    }
}
