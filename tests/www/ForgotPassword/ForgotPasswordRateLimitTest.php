<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace ForgotPassword;

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
use Rancoud\Security\Security;
use Rancoud\Session\Session;
use tests\Common;

/**
 * @internal
 */
class ForgotPasswordRateLimitTest extends TestCase
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
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token);
        SQL;

        $userParams = [
            'id'             => 20,
            'username'       => 'user_20',
            'hash'           => Crypt::hash('tgbrfvyhnuj'),
            'slug'           => 'user_20',
            'email'          => 'user_20@example.com',
            'grade'          => 'member',
            'remember_token' => null
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

        Session::set('phpunit_mail_sent', true);
        Session::set('phpunit_mail_called', 0);
        Session::set('phpunit_mail_html', static::getEmailHTML());
        Session::set('phpunit_mail_text', static::getEmailText());
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
    public function testForgotPasswordRateLimit(): void
    {
        // 1800 seconds - 1 forgot password per user
        // 1800 seconds - 10 forgot password per website

        // generate csrf
        $this->getResponseFromApplicationWithRateLimit('GET', '/', 'localhost');

        // put csrf
        $params['form-forgot_password-hidden-csrf'] = $_SESSION['csrf'];
        $params['form-forgot_password-input-email'] = 'user_20@example.com';

        // user 1 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsNotRateLimitedForUser($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');

        // user 1 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_1', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_1');
        $this->checkIsRateLimitedForUser($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');

        // user 2 - OK
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsNotRateLimitedForUser($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');

        // user 2 - KO
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_2', $params);
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'user_2');
        $this->checkIsRateLimitedForUser($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_3', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_4', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_5', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_6', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_7', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_8', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_9', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_10', $params);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsNotRateLimitedForGlobal($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');

        $this->getResponseFromApplicationWithRateLimit('POST', '/', 'user_11', $params);

        $response = $this->getResponseFromApplicationWithRateLimit('GET', '/', 'global');
        $this->checkIsRateLimitedForGlobal($response);
        static::$db->update('UPDATE users SET password_reset_at = NULL');
    }

    protected function checkIsNotRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not forgot password due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not forgot password due to rate limit specific to the website.');
    }

    protected function checkIsNotRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBodyNot($response, 'Error, could not forgot password due to rate limit specific to your IP address.');
        $this->doTestHtmlBodyNot($response, 'Error, could not forgot password due to rate limit specific to the website.');
    }

    protected function checkIsRateLimitedForUser(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not forgot password due to rate limit specific to your IP address.');
    }

    protected function checkIsRateLimitedForGlobal(Response $response): void
    {
        $this->doTestHtmlBody($response, 'Error, could not forgot password due to rate limit specific to the website.');
    }

    /** @throws \Exception */
    protected static function getEmailHTML(): string
    {
        \ob_start();
        $ds = \DIRECTORY_SEPARATOR;
        require \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'views/emails/forgot_password.html';

        $html = \ob_get_clean();

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $search = [
            '{{URL}}',
            '{{YEAR}}',
            '{{USERNAME}}',
            '{{SITE_NAME_HTML}}',
            '{{SITE_NAME_ATTR}}',
            '{{MAIL_HEADER_LOGO_PATH}}',
        ];

        $replace = [
            'https://blueprintue.test/reset-password/?reset_token={{TOKEN}}',
            $now->format('Y'),
            Security::escHTML('user_20'),
            Security::escHTML('this_site_name'),
            Security::escAttr('this_site_name'),
            Security::escAttr('https://blueprintue.test/full-logo.png'),
        ];

        return \str_replace($search, $replace, $html);
    }

    protected static function getEmailText(): string
    {
        $text = 'You have received this email because a password reset request was received for the account user_20. This link expires in 1 hour.' . "\n";
        $text .= 'Copy the URL below to complete the process:' . "\n\n";
        $text .= 'https://blueprintue.test/reset-password/?reset_token={{TOKEN}}' . "\n\n";
        $text .= 'If you did not request a password reset, no further action is required on your part.';

        return $text;
    }
}
