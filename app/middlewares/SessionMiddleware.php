<?php

declare(strict_types=1);

namespace app\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Crypt\Crypt;
use Rancoud\Session\Session;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Session\SessionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setDriver();

        $this->setReadWriteMode($request);

        $this->setOptions();

        $this->generateCsrf();

        $this->setUserIDInDatabase();

        return $handler->handle($request);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setDriver(): void
    {
        $sessionDriver = (string) Application::getConfig()->get('SESSION_DRIVER', 'default');
        $encryptionKey = (string) Application::getConfig()->get('SESSION_ENCRYPT_KEY');

        // @codeCoverageIgnoreStart
        if ($sessionDriver === 'database') {
            $db = Application::getDatabase();
            if (!empty($encryptionKey)) {
                Session::useCurrentDatabaseEncryptionDriver($db, $encryptionKey);
            } else {
                Session::useCurrentDatabaseDriver($db);
            }
        } elseif (!empty($encryptionKey)) {
            Session::useDefaultEncryptionDriver($encryptionKey);
        } else {
            Session::useDefaultDriver();
        }
        // @codeCoverageIgnoreEnd
    }

    // protected function

    protected function setReadWriteMode(ServerRequestInterface $request): void
    {
        Session::setReadWrite();
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Session\SessionException
     */
    protected function setOptions(): void
    {
        Session::setOption('gc_maxlifetime', (int) Application::getConfig()->get('SESSION_GC_MAXLIFETIME', 86400));
        Session::setOption('cookie_lifetime', (int) Application::getConfig()->get('SESSION_LIFETIME', 0));
        Session::setOption('cookie_path', (string) Application::getConfig()->get('SESSION_PATH', '/'));
        Session::setOption('cookie_secure', (bool) Application::getConfig()->get('SESSION_HTTPS', true));
        Session::setOption('cookie_samesite', (string) Application::getConfig()->get('SESSION_SAMESITE', 'Strict'));
    }

    /** @throws \Exception */
    protected function generateCsrf(): void
    {
        if (!Session::has('csrf')) {
            Session::set('csrf', Crypt::getRandomString());
        }
    }

    /** @throws \Exception */
    protected function setUserIDInDatabase(): void
    {
        $userID = (int) Session::get('userID');
        if ($userID > 0) {
            Session::setUserIdForDatabase($userID);
        }
    }
}
