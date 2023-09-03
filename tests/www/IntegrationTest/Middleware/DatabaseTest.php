<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Middleware;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Environment\Environment;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Session\Session;
use tests\Common;

class DatabaseTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Router\RouterException
     */
    public function testDatabase(): void
    {
        static::setDatabaseEmptyStructure();

        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__, 4),
            'ROUTES'  => \dirname(__DIR__, 4) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__, 4) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__, 4) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $env = new Environment(\dirname(__DIR__, 3), 'tests.env');

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';

        $app = new Application($folders, $env);

        $request = new ServerRequest('GET', '/');
        $response = $app->run($request);
        static::assertSame(200, $response->getStatusCode());

        Session::commit();

        $response = $app->run($request);
        static::assertSame(200, $response->getStatusCode());
    }
}
