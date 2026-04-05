<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace Login;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Crypt\CryptException;
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
class LoginRateLimitTest extends TestCase
{
    use Common;

    /**
     * @throws CryptException
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        $this->deleteRateLimitDatabase();

        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`, `confirmed_sent_at`, `confirmed_at`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token, :confirmed_sent_at, :confirmed_at);
        SQL;

        $userParams = [
            'id'                => 20,
            'username'          => 'user_20',
            'hash'              => Crypt::hash('qwerty'),
            'slug'              => 'user_20',
            'email'             => 'user_20@example.com',
            'grade'             => 'member',
            'remember_token'    => null,
            'confirmed_sent_at' => \gmdate('Y-m-d H:i:s'),
            'confirmed_at'      => \gmdate('Y-m-d H:i:s')
        ];
        static::$db->insert($sql, $userParams);
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
    public function testLoginRateLimitLoginSuccess(): void
    {
        // login error -> only count for user
        // login success -> user + global
        // 60 seconds - 3 login per user
        // 300 seconds - 5 login error per user
        // 60 seconds - 30 login per website

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // put csrf
        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-login-input-username'] = 'user_20';
        $params['form-login-input-password'] = 'qwerty';

        // user 1 - login success -> 3 login OK -> 4 login KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // user 2 banned - login success -> 3 login OK -> 4 login KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsRateLimitedForUser($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        $max = (30 - 5) + 2;
        for ($counter = 3; $counter < $max; ++$counter) {
            $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');
            $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
            $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_' . $counter, $params);
            $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_' . $counter);
            $this->checkIsNotRateLimitedForUser($response);
            $this->checkIsNotRateLimitedForGlobal($response);
            $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_' . $counter, ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
        }

        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');
        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_' . $counter, $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_' . $counter);
        $this->checkIsRateLimitedForGlobal($response);
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_' . $counter, ['form-logout-hidden-csrf' => $_SESSION['csrf']]);
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testLoginRateLimitLoginError(): void
    {
        // 300 seconds - 5 login error per user

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // put csrf
        $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-login-input-username'] = 'user_20';
        $params['form-login-input-password'] = '000';

        // user 1
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsRateLimitedErrorForUser($response);

        // user 2
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsRateLimitedErrorForUser($response);
    }

    protected function checkIsNotRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not login due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not login due to rate limit specific to the website.');
        $this->doTestHtmlBodyNot($response, 'Error, too many attempts.');
    }

    protected function checkIsNotRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not login due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not login due to rate limit specific to the website.');
        $this->doTestHtmlBodyNot($response, 'Error, too many attempts.');
    }

    protected function checkIsRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not login due to rate limit specific to your IP address.');
    }

    protected function checkIsRateLimitedErrorForUser(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, too many attempts.');
    }

    protected function checkIsRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not login due to rate limit specific to the website.');
    }
}
