<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
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

class BlueprintGETInformationsBlueprintTest extends TestCase
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
     * Use for testing informations about blueprint like thumbnail, type, title, exposure, expiration, ue version.
     *
     * @return array[]
     */
    public static function dataCasesBlueprintGET_InformationsBlueprint(): array
    {
        return [
            'no thumbnail / type blueprint / exposure public / no expiration / ue version 4.12' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_public',
                'thumbnail'  => null,
                'type'       => 'blueprint',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'public',
                'expiration' => null,
                'ueVersion'  => '4.12',
            ],
            'no thumbnail / type metasound / exposure public / no expiration / ue version 5.0' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'metasound', '5.0')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_public',
                'thumbnail'  => null,
                'type'       => 'metasound',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'public',
                'expiration' => null,
                'ueVersion'  => '5.0',
            ],
            'no thumbnail / type niagara / exposure public / no expiration / ue version 5.0' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'niagara', '5.0')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_public',
                'thumbnail'  => null,
                'type'       => 'niagara',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'public',
                'expiration' => null,
                'ueVersion'  => '5.0',
            ],
            'no thumbnail / type pcg / exposure public / no expiration / ue version 5.0' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'pcg', '5.0')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_public',
                'thumbnail'  => null,
                'type'       => 'pcg',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'public',
                'expiration' => null,
                'ueVersion'  => '5.0',
            ],
            'has thumbnail / type material / exposure unlisted / expiration in 1h / ue version 4.14' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, expiration, thumbnail) VALUES (1, 'slug_unlisted', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'material', '4.14', utc_timestamp() + interval 1 hour,  'thu\"><script>alert(1)</script>mbnail.jpg')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_unlisted',
                'thumbnail'  => '/medias/blueprints/thu"><script>alert(1)</script>mbnail.jpg',
                'type'       => 'material',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'unlisted',
                'expiration' => '59 min left',
                'ueVersion'  => '4.14',
            ],
            'no thumbnail / type animation / exposure private / expiration in 1d / ue version 4.16' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, expiration) VALUES (1, 'slug_private', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'private', 'animation', '4.16', utc_timestamp() + interval 1 day)",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_private',
                'thumbnail'  => null,
                'type'       => 'animation',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'private',
                'expiration' => '1 days left',
                'ueVersion'  => '4.16',
            ],
            'no thumbnail / type behavior_tree / exposure public / expiration in 1w / ue version 4.10' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, expiration) VALUES (1, 'slug_public_2', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'behavior_tree', '4.10', utc_timestamp() + interval 1 week)",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'       => 'slug_public_2',
                'thumbnail'  => null,
                'type'       => 'behavior tree',
                'title'      => '<script>alert(1)</script>my title',
                'exposure'   => 'public',
                'expiration' => '7 days left',
                'ueVersion'  => '4.10',
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintGET_InformationsBlueprint
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesBlueprintGET_InformationsBlueprint')]
    public function testBlueprintGETInformationsBlueprint(array $sqlQueries, string $slug, ?string $thumbnail, string $type, string $title, string $exposure, ?string $expiration, string $ueVersion): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // trick for private exposure access
        if ($exposure === 'private') {
            // set user session
            $sessionValues = [
                'set'    => ['userID' => 1],
                'remove' => []
            ];

            // generate csrf
            $this->getResponseFromApplication('GET', '/', [], $sessionValues);
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // thumbnail
        if ($thumbnail !== null) {
            $this->doTestHtmlMain($response, '<img alt="thumbnail blueprint" class="blueprint__avatar-container" src="' . Security::escAttr($thumbnail) . '"/>');
        } else {
            $this->doTestHtmlMainNot($response, '<img alt="thumbnail blueprint" class="blueprint__avatar-container" src="' . Security::escAttr($thumbnail) . '"/>');
        }

        // type
        $this->doTestHtmlMain($response, '<span class="blueprint__type">' . Security::escHTML($type) . '</span>');

        // title
        $this->doTestHtmlMain($response, '<h1 class="blueprint__title">' . Security::escHTML($title) . '</h1>');

        // exposure
        $this->doTestHtmlMain($response, '<li class="blueprint__property">Exposure: <span class="blueprint__property--emphasis">' . Security::escHTML($exposure) . '</span></li>');

        // expiration
        if ($expiration !== null) {
            try {
                $this->doTestHtmlMain($response, '<li class="blueprint__property">Expiration: <span class="blueprint__property--emphasis">' . Security::escHTML($expiration) . '</span></li>');
            } catch (\Exception $e) {
                if ($expiration === '7 days left') {
                    $expiration = '6 days left';
                } elseif ($expiration === '1 days left') {
                    $expiration = '23 h and 59 min left';
                }

                $this->doTestHtmlMain($response, '<li class="blueprint__property">Expiration: <span class="blueprint__property--emphasis">' . Security::escHTML($expiration) . '</span></li>');
            }
        } else {
            $this->doTestHtmlMainNot($response, '<li class="blueprint__property">Expiration: <span class="blueprint__property--emphasis">');
        }

        // ue version
        $this->doTestHtmlMain($response, '<li class="blueprint__property">UE Version: <span class="blueprint__property--emphasis">' . Security::escHTML($ueVersion) . '</span></li>');
    }
}
