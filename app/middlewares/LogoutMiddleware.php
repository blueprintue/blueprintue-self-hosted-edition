<?php

declare(strict_types=1);

namespace app\middlewares;

use app\controllers\FormTrait;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Session\Session;

class LogoutMiddleware implements MiddlewareInterface
{
    use FormTrait;

    protected array $inputs = [
        'CSRF' => 'form-logout-hidden-csrf',
    ];

    /**
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID') === false) {
            return $handler->handle($request);
        }

        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-logout')) {
            return $this->doProcessLogout($request);
        }

        return $handler->handle($request);
    }

    /**
     * @throws \Exception
     *
     * @return ResponseInterface|null
     */
    protected function doProcessLogout(ServerRequestInterface $request): ResponseInterface
    {
        $baseUri = (new Factory())->createUri($request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        $uriSuccess = (string) $baseUri->withFragment('logout-success');

        UserService::deleteRememberToken(Session::get('userID'));

        Session::destroy();

        \setcookie(
            (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token'),
            '',
            [
                'expires' => \time() - 3600 * 24 * 30,
            ]
        );

        return (new Factory())->createResponse(301)->withHeader('Location', $uriSuccess);
    }
}
