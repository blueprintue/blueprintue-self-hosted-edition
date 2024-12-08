<?php

declare(strict_types=1);

namespace app\middlewares;

use app\controllers\FormTrait;
use app\helpers\Helper;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Crypt\Crypt;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Session\Session;

class LoginMiddleware implements MiddlewareInterface
{
    use FormTrait;

    protected array $inputs = [
        'CSRF'     => 'form-login-hidden-csrf',
        'username' => 'form-login-input-username',
        'password' => 'form-login-input-password'
    ];

    protected string $rememberInputCheckbox = 'form-login-checkbox-remember';

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID')) {
            return $handler->handle($request);
        }

        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-login')) {
            $cleanedParams = $this->treatFormLogin($request);

            return $this->doProcessLogin($request, $cleanedParams);
        }

        $response = $this->tryToRememberUser($request);
        if ($response !== null) {
            return $response;
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws \Exception
     *
     * @return array|null
     */
    protected function treatFormLogin(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $hasErrors = false;
        $values = [];

        // username
        $values['username'] = $params[$this->inputs['username']];
        if ($values['username'] === '') {
            $hasErrors = true;
        }

        // password
        $values['password'] = $params[$this->inputs['password']];
        if ($values['password'] === '') {
            $hasErrors = true;
        }

        if ($hasErrors) {
            Session::setFlash('error-form-login', 'Error, invalid credentials');
            Session::keepFlash(['error-form-login']);

            return null;
        }

        return $values;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array|null             $params
     *
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     *
     * @return ResponseInterface|null
     */
    protected function doProcessLogin(ServerRequestInterface $request, ?array $params): ResponseInterface
    {
        $baseUri = (new Factory())->createUri($request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        $uriError = (string) $baseUri->withFragment('popin-login');
        $uriSuccess = (string) $baseUri->withFragment('login-success');

        if ($params === null) {
            return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
        }

        $forceRollback = false;
        $errorCode = '#100';
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $userID = UserService::findUserIDWithUsernameAndPassword($params['username'], $params['password']);
            if ($userID === null || $userID === (int) Application::getConfig()->get('ANONYMOUS_ID')) {
                $forceRollback = true;

                Session::setFlash('error-form-login', 'Error, invalid credentials');
                Session::keepFlash(['error-form-login']);

                return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
            }

            $errorCode = '#200';

            [$isConfirmedAccount, $hasToSendEmail] = UserService::isUserConfirmedAccount($userID);
            if (!$isConfirmedAccount) {
                $errorCode = '#250';

                if ($hasToSendEmail) {
                    UserService::generateAndSendConfirmAccountEmail($userID, 'login');
                }

                return (new Factory())->createResponse(301)->withHeader('Location', '/confirm-account/');
            }

            $errorCode = '#300';

            if ($this->login($userID) === false) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because check user ID has been done
                 * For covering we have to test the function outside
                 */
                throw new \Exception('Error, could not login');
                // @codeCoverageIgnoreEnd
            }

            $errorCode = '#400';

            if (isset($request->getParsedBody()[$this->rememberInputCheckbox])) {
                $errorCode = '#410';
                $this->generateRememberToken($userID, $request);
            } else {
                $errorCode = '#420';
                $this->deleteRememberToken($userID);
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because:
             * 1. check user ID has been done
             * 2. simple database actions is made on users table
             * For covering we have to simulate crash database
             */
            $forceRollback = true;

            Session::setFlash('error-form-login', 'Error, could not login (' . $errorCode . ')');
            Session::keepFlash(['error-form-login']);

            return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
            // @codeCoverageIgnoreEnd
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return (new Factory())->createResponse(301)->withHeader('Location', $uriSuccess);
    }

    /**
     * @param int $userID
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     *
     * @return bool
     */
    public function login(int $userID): bool
    {
        $userInfos = UserService::getInfosForSession($userID);
        if ($userInfos === null) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check user ID has been done
             * For covering we have to test the function outside
             */
            return false;
            // @codeCoverageIgnoreEnd
        }

        UserService::saveLastLogin($userID);

        Session::regenerate();
        Session::setUserIdForDatabase($userID);
        Session::set('userID', $userID);
        Session::set('username', $userInfos['username']);
        Session::set('grade', $userInfos['grade']);
        Session::set('slug', $userInfos['slug']);
        Session::set('csrf', Crypt::getRandomString());

        return true;
    }

    /**
     * @param int                    $userID
     * @param ServerRequestInterface $request
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     */
    protected function generateRememberToken(int $userID, ServerRequestInterface $request): void
    {
        $rememberToken = UserService::generateRememberToken($userID);
        \setcookie(
            (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token'),
            $rememberToken,
            [
                'expires'  => \time() + (int) Application::getConfig()->get('SESSION_REMEMBER_LIFETIME', 3600 * 24 * 30), // phpcs:ignore
                'path'     => (string) Application::getConfig()->get('SESSION_REMEMBER_PATH', '/'),
                'domain'   => $request->getUri()->getHost(),
                'secure'   => (bool) Application::getConfig()->get('SESSION_REMEMBER_HTTPS', true),
                'httponly' => true,
                'samesite' => (string) Application::getConfig()->get('SESSION_REMEMBER_SAMESITE', 'Strict'),
            ]
        );
    }

    /**
     * @param int $userID
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Database\DatabaseException
     */
    protected function deleteRememberToken(int $userID): void
    {
        UserService::deleteRememberToken($userID);

        \setcookie(
            (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token'),
            '',
            [
                'expires' => \time() - 3600 * 24 * 30,
            ]
        );
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Exception
     *
     * @return ResponseInterface|null
     */
    protected function tryToRememberUser(ServerRequestInterface $request): ?ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $cookieName = (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token');
        if (!isset($cookies[$cookieName])) {
            return null;
        }

        $userID = UserService::getUserIDFromRememberMe($cookies[$cookieName]);
        if ($userID === null) {
            return null;
        }

        if ($this->login($userID)) {
            $this->generateRememberToken($userID, $request);
        }

        $baseUri = (new Factory())->createUri($request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        $uriSuccess = (string) $baseUri->withFragment('login-success');

        return (new Factory())->createResponse(301)->withHeader('Location', $uriSuccess);
    }
}
