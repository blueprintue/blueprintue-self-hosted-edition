<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace Register;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
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
class RegisterRateLimitTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
    protected function setUp(): void
    {
        $this->deleteRateLimitDatabase();

        static::setDatabaseEmptyStructure();
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

        Session::set('phpunit_mail_sent', true);
        Session::set('phpunit_mail_called', 0);
        Session::set('phpunit_mail_html', static::getEmailHTMLConfirmAccount('a-zA-Z0-9._ -'));
        Session::set('phpunit_mail_text', static::getEmailTextConfirmAccount('a-zA-Z0-9._ -'));
        Session::set('phpunit_id_user', 1);
        Session::set('phpunit_return', true);

        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        return $response;
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testRegisterRateLimit(): void
    {
        // 1800 seconds - 1 register account per user
        // 1800 seconds - 10 register account per website

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // put csrf
        $params['form-register-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-register-input-username'] = 'a-zA-Z0-9._ -';
        $params['form-register-input-email'] = '0<script>alert("email");</script>@<script>alert("email");</script>';
        $params['form-register-input-subject'] = '';
        $params['form-register-input-password'] = '0<script>alert("Password");</script>';
        $params['form-register-input-password_confirm'] = '0<script>alert("Password");</script>';

        // user 1 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);
        static::$db->truncateTables('users', 'users_infos');

        // user 1 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsRateLimitedForUser($response);
        static::$db->truncateTables('users', 'users_infos');

        // user 2 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);
        static::$db->truncateTables('users', 'users_infos');

        // user 2 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsRateLimitedForUser($response);
        static::$db->truncateTables('users', 'users_infos');

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_3', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_4', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_5', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_6', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_7', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_8', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_9', $params);
        static::$db->truncateTables('users', 'users_infos');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_10', $params);
        static::$db->truncateTables('users', 'users_infos');
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsNotRateLimitedForGlobal($response);
        static::$db->truncateTables('users', 'users_infos');

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_11', $params);

        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsRateLimitedForGlobal($response);
        static::$db->truncateTables('users', 'users_infos');
    }

    protected function checkIsNotRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not create account due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not create account due to rate limit specific to the website.');
    }

    protected function checkIsNotRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not create account due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not create account due to rate limit specific to the website.');
    }

    protected function checkIsRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not create account due to rate limit specific to your IP address.');
    }

    protected function checkIsRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not create account due to rate limit specific to the website.');
    }
}
