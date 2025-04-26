<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\API;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\Environment;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Router\RouterException;
use tests\Common;

/** @internal */
class APIRenderTest extends TestCase
{
    use Common;

    /** @throws \Rancoud\Database\DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::$db->insert("INSERT INTO users (id, username, slug, created_at) VALUES (1, 'user_1', 'user_1', utc_timestamp())");
        static::$db->insert("INSERT INTO users_api (id_user, api_key) VALUES (1, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ')");
    }

    public static function provideDataCases(): iterable
    {
        yield 'render - OK' => [
            'headers' => [
                'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ],
            'params'  => [
                'blueprint' => 'begin object 1',
            ],
            'responseCode'    => 200,
            'responseContent' => <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>This is a base title</title>

    <meta name="robots" content="noindex">
    <meta content="No&#x20;description" name="description">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=5.0" name="viewport">

    <!--[if IE]>
    <meta HTTP-EQUIV="REFRESH" content="0; url=/ie.html">
    <![endif]-->

    <!-- favicons -->
    <link href="https&#x3A;&#x2F;&#x2F;blueprintue.test/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180">
    <link href="https&#x3A;&#x2F;&#x2F;blueprintue.test/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png">
    <link href="https&#x3A;&#x2F;&#x2F;blueprintue.test/favicon-16x16.png" rel="icon" sizes="16x16" type="image/png">
    <link crossorigin="use-credentials" href="https&#x3A;&#x2F;&#x2F;blueprintue.test/site.webmanifest" rel="manifest">
    <meta content="#1a1c1f" name="msapplication-TileColor">
    <meta content="#ffffff" name="theme-color">

    <link href="https&#x3A;&#x2F;&#x2F;blueprintue.test/bue-render/render.css" rel="stylesheet">
    <style>.hidden{display: none;}</style>
</head>
<body>
    <div class="playground"></div>
    <textarea class="hidden" id="pastebin_data">begin object 1</textarea>
    <script src="https&#x3A;&#x2F;&#x2F;blueprintue.test/bue-render/render.js"></script>
    <script>
        new window.blueprintUE.render.Main(
            document.getElementById('pastebin_data').value,
            document.getElementsByClassName('playground')[0],
            {height:"643px"}
        ).start();
    </script>
</body>
</html>

HTML,
        ];

        yield 'api key incorrect' => [
            'headers' => [
                'X-Token' => 'aaa'
            ],
            'params'  => [
                'blueprint' => 'begin object 1',
            ],
            'responseCode'    => 401,
            'responseContent' => '{"error":"api_key_incorrect"}',
        ];

        yield 'headers empty' => [
            'headers' => [],
            'params'  => [
                'blueprint' => 'begin object 1',
            ],
            'responseCode'    => 401,
            'responseContent' => '{"error":"api_key_empty"}',
        ];

        yield 'api key empty' => [
            'headers' => [
                'X-Token' => ''
            ],
            'params'  => [
                'blueprint' => 'begin object 1',
            ],
            'responseCode'    => 401,
            'responseContent' => '{"error":"api_key_empty"}',
        ];

        yield 'api key invalid encoding' => [
            'headers' => [
                'X-Token' => \chr(99999999)
            ],
            'params'  => [
                'blueprint' => 'begin object 1',
            ],
            'responseCode'    => 401,
            'responseContent' => '{"error":"api_key_incorrect"}',
        ];

        yield 'missing fields - no fields' => [
            'headers' => [
                'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ],
            'params'          => [],
            'responseCode'    => 400,
            'responseContent' => '{"error":"blueprint_empty"}',
        ];

        yield 'empty fields - blueprint empty' => [
            'headers' => [
                'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ],
            'params'  => [
                'blueprint' => ' ',
            ],
            'responseCode'    => 400,
            'responseContent' => '{"error":"blueprint_empty"}',
        ];

        yield 'invalid fields - blueprint' => [
            'headers' => [
                'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ],
            'params'  => [
                'blueprint' => 'aze',
            ],
            'responseCode'    => 400,
            'responseContent' => '{"error":"blueprint_empty"}',
        ];

        yield 'invalid encoding fields - blueprint' => [
            'headers' => [
                'X-Token' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            ],
            'params'  => [
                'blueprint' => \chr(99999999)
            ],
            'responseCode'    => 400,
            'responseContent' => '{"error":"blueprint_empty"}',
        ];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideDataCases')]
    public function testRenderPOST(array $headers, array $params, int $responseCode, string $responseContent): void
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

        $request = new ServerRequest('POST', '/api/render');
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $request = $request->withParsedBody($params);
        $response = $app->run($request);

        static::assertSame($responseCode, $response->getStatusCode());
        static::assertSame($responseContent, (string) $response->getBody());
    }
}
