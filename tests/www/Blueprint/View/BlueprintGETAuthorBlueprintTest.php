<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintGETAuthorBlueprintTest extends TestCase
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
     * Use for testing informations about author like avatar, name, date.
     *
     * @return array[]
     */
    public function dataCasesBlueprintGET_AuthorBlueprint(): array
    {
        $date = '2020-01-01 01:01:01';

        return [
            'no avatar' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), '" . $date . "', 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'         => 'slug_public',
                'avatar'       => null,
                'name'         => 'member',
                'url'          => '/profile/member/',
                'published_at' => \date('F j, Y, g:i a', \strtotime($date)),
            ],
            'has avatar' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), '" . $date . "', 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (1, 'mem<script>alert(1)</script>ber', null, 'mem\"><script>alert(1)</script>ber', 'member@mail', utc_timestamp(), 'ava\"><script>alert(1)</script>tar.jpg')",
                ],
                'slug'         => 'slug_public',
                'avatar'       => '/medias/avatars/ava"><script>alert(1)</script>tar.jpg',
                'name'         => 'mem<script>alert(1)</script>ber',
                'url'          => '/profile/mem"><script>alert(1)</script>ber/',
                'published_at' => \date('F j, Y, g:i a', \strtotime($date)),
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintGET_AuthorBlueprint
     *
     * @param array       $sqlQueries
     * @param string      $slug
     * @param string|null $avatar
     * @param string      $name
     * @param string      $url
     * @param string      $createdAt
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    public function testBlueprintGETAuthorBlueprint(array $sqlQueries, string $slug, ?string $avatar, string $name, string $url, string $createdAt): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // author
        $this->doTestHtmlMain($response, '<h2 class="blueprint__author"><a class="blueprint__profile" href="' . Security::escAttr($url) . '">' . Security::escHTML($name) . '</a></h2>');

        // author avatar
        if ($avatar !== null) {
            $this->doTestHtmlMain($response, '<img alt="avatar author" class="blueprint__avatar-container" src="' . Security::escAttr($avatar) . '"/>');
            $this->doTestHtmlMainNot($response, <<<HTML
<div class="blueprint__avatar-container blueprint__avatar-container--background">
<svg class="blueprint__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
HTML);
        } else {
            $this->doTestHtmlMainNot($response, '<img alt="avatar author" class="blueprint__avatar-container" src="');
            $this->doTestHtmlMain($response, <<<HTML
<div class="blueprint__avatar-container blueprint__avatar-container--background">
<svg class="blueprint__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
HTML);
        }

        // blueprint created at
        $this->doTestHtmlMain($response, '<p class="blueprint__time">' . Security::escHTML($createdAt) . '</p>');
    }
}
