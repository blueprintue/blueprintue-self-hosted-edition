<?php

declare(strict_types=1);

namespace tests\www\Cron;

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
class CronPurgeRateLimitEntriesTest extends TestCase
{
    use Common;

    /**
     * @throws \Exception
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    protected function getResponseFromApplicationWithRateLimit(string $method, string $url, array $params = []): ?Response
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
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testCronPurgeRateLimitEntriesGET(): void
    {
        $this->deleteRateLimitDatabase();

        $this->getResponseFromApplication('GET', '/cron/purge_rate_limit_entries/');

        static::assertFalse($this->isRateLimitDatabaseExist());

        $this->getResponseFromApplicationWithRateLimit('GET', '/cron/purge_rate_limit_entries/');

        static::assertTrue($this->isRateLimitDatabaseExist());

        $rateLimitDatabase = $this->getRateLimitDatabase();

        $now = \time();

        $sql = <<<'SQL'
        INSERT INTO `rate_limit`(`id`, `time`)
        VALUES ('now + 2d', :now_add_2d),
               ('now + 1d 1min', :now_add_1d1min),
               ('now + 23h', :now_add_23h),
               ('now', :now),
               ('now - 23h', :now_minus_23h),
               ('now - 1d 1min', :now_minus_1d1min),
               ('now - 2d', :now_minus_2d);
        SQL;

        $rateLimitDatabase->exec($sql, [
            'now_add_2d'       => $now + (2 * 24 * 60 * 60),
            'now_add_1d1min'   => $now + (24 * 60 * 60) + 60,
            'now_add_23h'      => $now + (23 * 60 * 60),
            'now'              => $now,
            'now_minus_23h'    => $now - (23 * 60 * 60),
            'now_minus_1d1min' => $now - (24 * 60 * 60) - 60,
            'now_minus_2d'     => $now - (2 * 24 * 60 * 60),
        ]);

        $entries = $rateLimitDatabase->selectCol('SELECT id FROM rate_limit ORDER BY time');
        $entriesLeft = [
            'now + 2d',
            'now + 1d 1min',
            'now + 23h',
            'now',
            'now - 23h',
            'now - 1d 1min',
            'now - 2d',
        ];
        static::assertEqualsCanonicalizing($entriesLeft, $entries);

        $this->getResponseFromApplicationWithRateLimit('GET', '/cron/purge_rate_limit_entries/');

        $entries = $rateLimitDatabase->selectCol('SELECT id FROM rate_limit ORDER BY time');
        $entriesLeft = [
            'now + 2d',
            'now + 1d 1min',
            'now + 23h',
            'now',
            'now - 23h',
        ];
        static::assertEqualsCanonicalizing($entriesLeft, $entries);
    }
}
