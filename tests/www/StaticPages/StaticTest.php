<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\StaticPages;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\Environment;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class StaticTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    protected function getResponseFromApplicationWithStatic(string $url): ?\Rancoud\Http\Message\Response
    {
        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__, 3),
            'ROUTES'  => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__, 3) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $env = new Environment(__DIR__, 'tests_static.env');

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';

        $app = new Application($folders, $env);

        // for better perf, reuse the same database connexion
        static::setDatabase();
        Application::setDatabase(static::$db);

        $request = new ServerRequest('GET', $url);
        $response = $app->run($request);

        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        return $response;
    }

    public static function provideDataCases(): iterable
    {
        return [
            'terms of service' => [
                'url'     => '/terms-of-service/',
                'headers' => [
                    'title'       => 'Terms of Service for &lt;strong&gt;this_site_name&lt;&#47;strong&gt; | &lt;strong&gt;This is a base title&lt;&#47;strong&gt;',
                    'description' => 'Terms&#x20;of&#x20;Service&#x20;for&#x20;&lt;strong&gt;this_site_name&lt;&#x2F;strong&gt;',
                ],
                'content' => '<h2 class="block__title">Terms of Service for &lt;strong&gt;this_site_name&lt;&#47;strong&gt;</h2>',
            ],
            'privacy policy' => [
                'url'     => '/privacy-policy/',
                'headers' => [
                    'title'       => 'Privacy Policy for &lt;strong&gt;this_site_name&lt;&#47;strong&gt; | &lt;strong&gt;This is a base title&lt;&#47;strong&gt;',
                    'description' => 'Privacy&#x20;Policy&#x20;for&#x20;&lt;strong&gt;this_site_name&lt;&#x2F;strong&gt;',
                ],
                'content' => '<h2 class="block__title">Privacy Policy for &lt;strong&gt;this_site_name&lt;&#47;strong&gt;</h2>',
            ]
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideDataCases')]
    public function testStaticPage(string $url, array $headers, string $content): void
    {
        $response = $this->getResponseFromApplicationWithStatic($url);

        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, $headers);
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);

        $this->doTestHtmlMain($response, $content);
    }
}
