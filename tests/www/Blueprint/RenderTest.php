<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint;

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

class RenderTest extends TestCase
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

    public static function dataCasesRenderGET_Access(): array
    {
        return [
            'no blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                ],
                'userID'            => null,
                'slug'              => 'slug_incorrect',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'no blueprints - no published_at - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), null, 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'no blueprints - public but expiration passed - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, expiration) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', '2020-01-01 01:01:01')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'no blueprints - deleted - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'no blueprints - missing blueprints_versions - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'no blueprints - invalid version - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug', 'file', 'title', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug/2',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'visitor user - public blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at) VALUES (1, 'slug_public', 'file', 'visitor user - public blueprint - OK', 1, utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug_public',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'visitor user - public blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'visitor user - unlisted blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'visitor user - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug_unlisted',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'visitor user - unlisted blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'visitor user - private blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_private', 'file', 'visitor user - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug_private',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'visitor user - deleted blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug_private', 'file', 'visitor user - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => null,
                'slug'              => 'slug_private',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'user lambda - public blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'user lambda - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 10,
                'slug'              => 'slug_public',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'user lambda - public blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'user lambda - unlisted blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'user lambda - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 10,
                'slug'              => 'slug_unlisted',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'user lambda - unlisted blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'user lambda - private blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_private', 'file', 'user lambda - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 10,
                'slug'              => 'slug_private',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'user lambda - deleted blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, deleted_at) VALUES (1, 'slug_private', 'file', 'user lambda - private blueprint - KO', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 10,
                'slug'              => 'slug_private',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
            'author - public blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'file', 'author - public blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 1,
                'slug'              => 'slug_public',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'author - public blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'author - unlisted blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'file', 'author - unlisted blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 1,
                'slug'              => 'slug_unlisted',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'author - unlisted blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'No description provided',
            ],
            'author - private blueprint - OK' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 1,
                'slug'              => 'slug_private',
                'statusCode'        => 200,
                'location'          => null,
                'headerTitle'       => 'author - private blueprint - OK posted by member | This is a base title',
                'headerDescription' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has',
            ],
            'author - deleted blueprint - KO' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, description, deleted_at) VALUES (1, 'slug_private', 'file', 'author - private blueprint - OK', 1, utc_timestamp(), utc_timestamp(), 'private', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.', utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'userID'            => 1,
                'slug'              => 'slug_private',
                'statusCode'        => 301,
                'location'          => '/',
                'headerTitle'       => null,
                'headerDescription' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesRenderGET_Access
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesRenderGET_Access')]
    public function testRenderGETAccess(array $sqlQueries, ?int $userID, string $slug, int $statusCode, ?string $location, ?string $headerTitle, ?string $headerDescription): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user in $_SESSION
        $session = ['remove' => [], 'set' => []];
        if ($userID !== null) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // get blueprint
        $response = $this->getResponseFromApplication('GET', '/render/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, $statusCode);
        if ($location !== null) {
            static::assertSame($location, $response->getHeaderLine('Location'));
        }

        if ($statusCode !== 200) {
            return;
        }

        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($headerTitle),
            'description' => Security::escAttr($headerDescription),
        ]);

        $this->doTestHtmlBody($response, <<<HTML
<body>
<div class="playground"></div>
<textarea class="hidden" id="pastebin_data"></textarea>
<script src="https&#x3A;&#x2F;&#x2F;blueprintue.test/bue-render/render.js"></script>
<script>
new window.blueprintUE.render.Main(
document.getElementById('pastebin_data').value,
document.getElementsByClassName('playground')[0],
{height:"643px"}
).start();
</script>
</body>
HTML);
    }

    /**
     * Use for testing content inside iframe, code to copy, code to embed
     * Also test for switching between old (fileID-15.txt) and new file versioning (fileID-15.0.0.txt).
     *
     * @return array[]
     */
    public static function dataCasesRenderGET_FileBlueprint(): array
    {
        return [
            'get last version : file 1' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.10')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'fileID'  => 'a',
                'version' => '1',
                'slug'    => 'slug_public',
            ],
            'get specific version : file 3' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 3, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.10')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 3, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'fileID'  => 'a',
                'version' => '3',
                'slug'    => 'slug_public/3',
            ],
            'missing file' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 2, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.10')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'fileID'  => 'a',
                'version' => 'missing',
                'slug'    => 'slug_public',
            ],
        ];
    }

    /**
     * @dataProvider dataCasesRenderGET_FileBlueprint
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesRenderGET_FileBlueprint')]
    public function testRenderGETFileBlueprint(array $sqlQueries, string $fileID, string $version, string $slug): void
    {
        static::cleanFiles();

        // init file blueprint
        $content = $this->createBlueprintFile($fileID, $version);
        if ($version === 'missing') {
            $content = '';
        }

        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/render/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        $v = Security::escHTML($content);
        $this->doTestHtmlBody($response, <<<HTML
<body>
<div class="playground"></div>
<textarea class="hidden" id="pastebin_data">$v</textarea>
<script src="https&#x3A;&#x2F;&#x2F;blueprintue.test/bue-render/render.js"></script>
<script>
new window.blueprintUE.render.Main(
document.getElementById('pastebin_data').value,
document.getElementsByClassName('playground')[0],
{height:"643px"}
).start();
</script>
</body>
HTML);
    }
}
