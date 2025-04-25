<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\API;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\Environment;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Router\RouterException;
use tests\Common;

class APIUploadTest extends TestCase
{
    use Common;

    /** @throws \Rancoud\Database\DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::$db->insert("INSERT INTO users (id, username, slug, created_at) VALUES (1, 'user_1', 'user_1', utc_timestamp())");
        static::$db->insert("INSERT INTO users_api (id_user, api_key) VALUES (1, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')");
        static::$db->insert('INSERT INTO users_infos (id_user) VALUES (1)');
    }

    public static function dataCases(): array
    {
        return [
            'upload - OK' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'     => 'my title',
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 200,
                'responseContent' => '{"key":"xxxxxxxx"}',
            ],
            'upload with extra infos - OK' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title 2',
                    'blueprint'  => 'begin object 2',
                    'exposure'   => 'private',
                    'expiration' => '3600',
                    'version'    => '4.12',
                ],
                'responseCode'    => 200,
                'responseContent' => '{"key":"xxxxxxxx"}',
            ],
            'api key incorrect' => [
                'headers' => [
                    'X-Token' => 'aaa'
                ],
                'params'  => [
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 401,
                'responseContent' => '{"error":"api_key_incorrect"}',
            ],
            'headers empty' => [
                'headers' => [],
                'params'  => [
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 401,
                'responseContent' => '{"error":"api_key_empty"}',
            ],
            'api key empty' => [
                'headers' => [
                    'X-Token' => ''
                ],
                'params'  => [
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 401,
                'responseContent' => '{"error":"api_key_empty"}',
            ],
            'api key invalid encoding' => [
                'headers' => [
                    'X-Token' => \chr(99999999)
                ],
                'params'  => [
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 401,
                'responseContent' => '{"error":"api_key_incorrect"}',
            ],
            'missing fields - no fields' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'           => [],
                'responseCode'     => 400,
                'responseContent'  => '{"error":"required_title"}',
            ],
            'missing fields - no title' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'           => [
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"required_title"}',
            ],
            'missing fields - no blueprint' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'           => [
                    'title'     => 'my title',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_blueprint"}',
            ],
            'empty fields - title empty' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'     => ' ',
                    'blueprint' => 'begin object 1',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"required_title"}',
            ],
            'empty fields - blueprint empty' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'     => 'my title',
                    'blueprint' => ' ',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_blueprint"}',
            ],
            'invalid fields - blueprint' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'     => 'my title',
                    'blueprint' => 'aze',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_blueprint"}',
            ],
            'invalid fields - exposure' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => 'begin object 1',
                    'exposure'   => 'xxx',
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_exposure"}',
            ],
            'invalid fields - expiration' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => 'begin object 1',
                    'expiration' => 'xxx'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_expiration"}',
            ],
            'invalid fields - version' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => 'begin object 1',
                    'version'    => 'xxx',
                    'expiration' => '604800'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid_version"}',
            ],
            'do throw exception' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'              => 'my title',
                    'blueprint'          => 'begin object 1',
                    'do throw exception' => 'do throw exception'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"error_insert_blueprint_#200"}',
            ],
            'invalid encoding fields - title' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => \chr(99999999),
                    'blueprint'  => 'begin object 1',
                    'version'    => 'public',
                    'expiration' => '604800'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid"}',
            ],
            'invalid encoding fields - blueprint' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => \chr(99999999),
                    'version'    => 'public',
                    'expiration' => '604800'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid"}',
            ],
            'invalid encoding fields - version' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => 'begin object 1',
                    'version'    => \chr(99999999),
                    'expiration' => '604800'
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid"}',
            ],
            'invalid encoding fields - expiration' => [
                'headers' => [
                    'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                ],
                'params'  => [
                    'title'      => 'my title',
                    'blueprint'  => 'begin object 1',
                    'version'    => 'public',
                    'expiration' => \chr(99999999)
                ],
                'responseCode'    => 400,
                'responseContent' => '{"error":"invalid"}',
            ]
        ];
    }

    /**
     * @dataProvider dataCases
     *
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('dataCases')]
    public function testUploadPOST(array $headers, array $params, int $responseCode, string $responseContent): void
    {
        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__, 3),
            'ROUTES'  => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__, 3) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $env = new Environment(\dirname(__DIR__, 2), 'tests.env');

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';

        $app = new Application($folders, $env);

        // for better perf, reuse the same database connexion
        static::setDatabase();
        Application::setDatabase(static::$db);

        $request = new ServerRequest('POST', '/api/upload');
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $userBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = 1');

        $request = $request->withParsedBody($params);

        if (isset($params['do throw exception']) && $params['do throw exception'] === 'do throw exception') {
            static::$db->exec('UPDATE blueprints SET expiration = utc_timestamp() WHERE id > 0');
            static::$db->exec('ALTER TABLE blueprints CHANGE COLUMN `expiration` `expiration` DATETIME NOT NULL ;');
        }

        $response = $app->run($request);

        if (isset($params['do throw exception']) && $params['do throw exception'] === 'do throw exception') {
            static::$db->exec('ALTER TABLE blueprints CHANGE COLUMN `expiration` `expiration` DATETIME NULL ;');
        }

        static::assertSame($responseCode, $response->getStatusCode());
        if ($responseCode !== 200) {
            static::assertSame($responseContent, (string) $response->getBody());

            $userAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = 1');
            static::assertEqualsCanonicalizing($userAfter, $userBefore);
        } else {
            $rp = \json_decode((string) $response->getBody(), true, 512, \JSON_THROW_ON_ERROR);

            // user
            $userAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = 1');
            static::assertNotEqualsCanonicalizing($userAfter, $userBefore);

            // blueprint
            $blueprint = static::$db->selectRow('SELECT * FROM blueprints WHERE slug = :slug', ['slug' => $rp['key']]);
            if (!isset($params['exposure'])) {
                $params['exposure'] = 'public';
            }
            if (!isset($params['version'])) {
                $params['version'] = Helper::getCurrentUEVersion();
            }

            static::assertSame($params['exposure'], $blueprint['exposure']);
            static::assertSame($params['version'], $blueprint['ue_version']);

            if (!isset($params['expiration'])) {
                static::assertNull($blueprint['expiration']);
            } else {
                static::assertNotNull($blueprint['expiration']);
            }
        }
    }
}
