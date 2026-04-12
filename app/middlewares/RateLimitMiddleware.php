<?php

declare(strict_types=1);

namespace app\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\Configurator;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;

class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Application::getConfig()->get('RATE_LIMIT_DISABLE', false)) {
            return $handler->handle($request);
        }

        $this->createStorageForRateLimit();

        return $handler->handle($request);
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    protected function createStorageForRateLimit(): void
    {
        $root = Application::getFolder('ROOT');
        $db = $root . \DIRECTORY_SEPARATOR . 'rate_limit.db';
        $params = [
            'driver'    => 'sqlite',
            'host'      => '',
            'user'      => Application::getConfig()->get('RATE_LIMIT_DB_USER', ''),
            'password'  => Application::getConfig()->get('RATE_LIMIT_DB_PASSWORD', ''),
            'database'  => $db
        ];

        $rateLimitDB = new Database(new Configurator($params));

        $sql = <<<'SQL'
        CREATE TABLE IF NOT EXISTS `rate_limit` (
            `id` varchar(128) NOT NULL,
            `time` timestamp NOT NULL
        );
        SQL;

        $rateLimitDB->exec($sql);

        Application::setInBag('rate_limit', $rateLimitDB);
    }
}
