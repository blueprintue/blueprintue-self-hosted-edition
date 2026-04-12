<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace Contact;

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
class ContactRateLimitTest extends TestCase
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

        Session::set('phpunit_mail_sent', true);
        Session::set('phpunit_mail_called', 0);
        Session::set('phpunit_mail_text', "Name: 20\nEmail: 20@0\nMessage: 20");

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
    public function testContactRateLimit(): void
    {
        // 1800 seconds - 1 email per user
        // 1800 seconds - 10 email per website

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'localhost');

        // put csrf
        $params['form-contact-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-contact-input-name'] = '20';
        $params['form-contact-input-email'] = '20@0';
        $params['form-contact-textarea-message'] = '20';

        // user 1 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);

        // user 1 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'user_1');
        $this->checkIsRateLimitedForUser($response);

        // user 2 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);

        // user 2 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'user_2');
        $this->checkIsRateLimitedForUser($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_3', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_4', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_5', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_6', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_7', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_8', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_9', $params);
        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_10', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'global');
        $this->checkIsNotRateLimitedForGlobal($response);

        $this->getResponseFromApplicationWithRateLimit('POST', '/contact/', 'user_11', $params);

        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/contact/', 'global');
        $this->checkIsRateLimitedForGlobal($response);
    }

    protected function checkIsNotRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Message sent successfully');
        $this->doTestHtmlMainNot($response, 'Error, could not send mail due to rate limit specific to your IP address.');
    }

    protected function checkIsNotRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Message sent successfully');
        $this->doTestHtmlMainNot($response, 'Error, could not send mail due to rate limit specific to the website.');
    }

    protected function checkIsRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Error, could not send mail due to rate limit specific to your IP address.');
        $this->doTestHtmlMainNot($response, 'Message sent successfully');
    }

    protected function checkIsRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlMain($response, 'Error, could not send mail due to rate limit specific to the website.');
        $this->doTestHtmlMainNot($response, 'Message sent successfully');
    }
}
