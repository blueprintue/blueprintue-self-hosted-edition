<?php

declare(strict_types=1);

namespace app\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Database\Configurator;

class DatabaseMiddleware implements MiddlewareInterface
{
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $db = Application::getDatabase();
        if ($db !== null) {
            return $handler->handle($request);
        }

        $configurator = $this->createConfigurator();
        $database = new \Rancoud\Database\Database($configurator);
        Application::setDatabase($database);

        return $handler->handle($request);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function createConfigurator(): Configurator
    {
        $config = Application::getConfig();
        $params = [
            'driver'                => (string) $config->get('DATABASE_DRIVER'),
            'host'                  => (string) $config->get('DATABASE_HOST'),
            'user'                  => (string) $config->get('DATABASE_USER'),
            'password'              => (string) $config->get('DATABASE_PASSWORD'),
            'database'              => (string) $config->get('DATABASE_NAME'),
            'persistent_connection' => (bool) $config->get('DATABASE_PERSISTENT_CONNECTION', false)
        ];

        return new Configurator($params);
    }
}
