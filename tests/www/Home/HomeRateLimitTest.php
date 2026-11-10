<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Home;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Environment\Environment;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\Response;
use Rancoud\Http\Message\ServerRequest;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

/**
 * @internal
 */
class HomeRateLimitTest extends TestCase
{
    use Common;

    protected function setUp(): void
    {
        $this->deleteRateLimitDatabase();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        $this->deleteRateLimitDatabase();
    }

    /**
     * @throws \Exception
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    protected function getResponseFromApplicationWithRateLimit(string $method, string $url, string $ip, array $params = []): ?Response
    {
        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__, 3),
            'ROUTES'  => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__, 3) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $env = new Environment(\dirname(__DIR__, 2), 'tests-with-rate-limit.env');

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';
        $_SERVER['REMOTE_ADDR'] = $ip;

        $app = new Application($folders, $env);

        // for better perf, reuse the same database connexion
        static::setDatabase();
        Application::setDatabase(static::$db);

        $request = new ServerRequest($method, $url, [], null, '1.1', $_SERVER);
        if (\count($params) > 0) {
            $request = $request->withParsedBody($params);
        }

        $response = $app->run($request);

        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        return $response;
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testHomeAnonymousRateLimit(): void
    {
        // 1800 seconds - 3 blueprints created per anonymous user
        // 1800 seconds - 15 blueprints created per anonymous user per website

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // put csrf
        $params['form-add_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-add_blueprint-input-title'] = 'title';
        $params['form-add_blueprint-select-exposure'] = 'unlisted';
        $params['form-add_blueprint-select-expiration'] = '1w';
        $params['form-add_blueprint-select-ue_version'] = '4.14';
        $params['form-add_blueprint-textarea-blueprint'] = 'Begin Object 3';

        // user 1 - 1 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        // user 1 - 2 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        // user 1 - 3 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        // user 1 - 4 blueprint - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsRateLimitedForUser($response);

        // user 2 - 1 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        // user 2 - 2 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        // user 2 - 3 blueprint - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        // user 2 - 4 blueprint - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_3', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_4', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_5', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_6', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_7', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_8', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_9', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_10', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_11', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsNotRateLimitedForGlobal($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_11', $params);

        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsRateLimitedForGlobal($response);
    }

    protected function checkIsNotRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlMainNot($response, 'Error, could not create anonymous blueprint due to rate limit specific to your IP address.');
    }

    protected function checkIsNotRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlMainNot($response, 'Error, could not create anonymous blueprint due to rate limit specific to the website.');
    }

    protected function checkIsRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Error, could not create anonymous blueprint due to rate limit specific to your IP address.');
    }

    protected function checkIsRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Error, could not create anonymous blueprint due to rate limit specific to the website.');
    }
}
