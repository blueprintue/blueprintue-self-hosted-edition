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

class BlueprintGETFileBlueprintTest extends TestCase
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
     * Use for testing content inside iframe, code to copy, code to embed
     * Also test for switching between old (fileID-15.txt) and new file versioning (fileID-15.0.0.txt).
     *
     * @return array[]
     */
    public static function dataCasesBlueprintGET_FileBlueprint(): array
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
     * @dataProvider dataCasesBlueprintGET_FileBlueprint
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesBlueprintGET_FileBlueprint')]
    public function testBlueprintGETFileBlueprint(array $sqlQueries, string $fileID, string $version, string $slug): void
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
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // code to copy
        $this->doTestHtmlMain($response, '<textarea class="form__input form__input--textarea blueprint__code-copy-textarea blueprint__code-copy-textarea--hidden" id="code_to_copy">' . Security::escHTML($content) . '</textarea>');

        // code to embed
        /* @noinspection HtmlDeprecatedAttribute */
        $this->doTestHtmlMain($response, '<input class="form__input" id="code_to_embed" type="text" value="' . Security::escAttr('<iframe src="' . $this->getHostname() . '/render/' . $slug . '/" scrolling="no" allowfullscreen></iframe>') . '"/>');
    }
}
