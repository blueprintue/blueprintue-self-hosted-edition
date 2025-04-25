<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Blueprint\Edit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintEditGETTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Crypt\CryptException
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 189,
            'username' => 'user_189',
            'hash'     => Crypt::hash('password_user_189'),
            'slug'     => 'user_189',
            'email'    => 'user_189@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCasesAccess(): array
    {
        return [
            'redirect - blueprint not exist' => [
                'sqlQueries'  => [],
                'slug'        => '/blueprint/4564879864564/edit/',
                'location'    => '/blueprint/4564879864564/',
                'userID'      => null,
                'contentHead' => null,
            ],
            'redirect - visitor' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public')",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => null,
                'contentHead' => null,
            ],
            'redirect - user connected' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public')",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => 199,
                'contentHead' => null,
            ],
            'redirect - anonymous user connected (not possible)' => [
                'sqlQueries'  => [],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => 2,
                'contentHead' => null,
            ],
            'redirect - user connected but not exists in database (not possible)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public')",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => 50,
                'contentHead' => null,
            ],
            'redirect - user is author - blueprint deleted' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => 189,
                'contentHead' => null,
            ],
            'redirect - user is author - blueprint expired' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, expiration) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp() - interval 1 day)",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => '/blueprint/slug_public/',
                'userID'      => 189,
                'contentHead' => null,
            ],
            'OK - user is author - no thumbnail' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => null,
                'userID'      => 189,
                'contentHead' => [
                    'title'       => 'Edit blueprint title_public | This is a base title',
                    'description' => 'Edit blueprint title_public'
                ],
            ],
            'OK - user is author - has thumbnail' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '<script>alert(1)</script>')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => null,
                'userID'      => 189,
                'contentHead' => [
                    'title'       => 'Edit blueprint title_public | This is a base title',
                    'description' => 'Edit blueprint title_public'
                ],
            ],
            'OK - user is author - has thumbnail (private exposure)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'private', '<script>alert(1)</script>')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'slug'        => '/blueprint/slug_public/edit/',
                'location'    => null,
                'userID'      => 189,
                'contentHead' => [
                    'title'       => 'Edit blueprint title_public | This is a base title',
                    'description' => 'Edit blueprint title_public'
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesAccess
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesAccess')]
    public function testBlueprintEditGET(array $sqlQueries, string $slug, ?string $location, ?int $userID, ?array $contentHead): void
    {
        static::setDatabase();
        static::$db->truncateTables('blueprints', 'blueprints_version');

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => [],
            'remove' => ['userID']
        ];

        if ($userID !== null) {
            $sessionValues = [
                'set'    => ['userID' => $userID],
                'remove' => []
            ];
        }

        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        $response = $this->getResponseFromApplication('GET', $slug);
        if ($location !== null) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            static::assertSame($location, $response->getHeaderLine('Location'));

            return;
        }

        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($contentHead['title']),
            'description' => Security::escAttr($contentHead['description'])
        ]);
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);

        $thumbnail = static::$db->selectVar('SELECT thumbnail FROM blueprints WHERE id = 1');
        // verif thumbnail
        if ($thumbnail === null) {
            /* @noinspection RequiredAttributes */
            $this->doTestHtmlBody($response, <<<'HTML'
<div class="profile__avatar-container" id="current-thumbnail">
<img alt="blueprint thumbnail" class="profile__avatar-container profile__avatar-container--hidden" id="upload-current-thumbnail"/>
<div class="profile__avatar-container profile__avatar-container--background" id="upload-fallback-thumbnail">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
HTML);
        } else {
            $v = Security::escAttr($thumbnail);
            $this->doTestHtmlBody($response, <<<HTML
<div class="profile__avatar-container" id="current-thumbnail">
<img alt="blueprint thumbnail" class="profile__avatar-container" id="upload-current-thumbnail" src="&#x2F;medias&#x2F;blueprints&#x2F;{$v}"/>
</div>
HTML);
        }
    }
}
