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
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Session\Session;

class RegisterMiddleware implements MiddlewareInterface
{
    use FormTrait;

    protected array $inputs = [
        'CSRF'             => 'form-register-hidden-csrf',
        'username'         => 'form-register-input-username',
        'email'            => 'form-register-input-email',
        'password'         => 'form-register-input-password',
        'password_confirm' => 'form-register-input-password_confirm'
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID')) {
            return $handler->handle($request);
        }

        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-register')) {
            $cleanedParams = $this->treatFormRegister($request);

            return $this->doProcessRegister($request, $cleanedParams);
        }

        return $handler->handle($request);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    protected function treatFormRegister(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // username
        $values['username'] = $params[$this->inputs['username']];
        if ($values['username'] === '') {
            $errorsForMessage[] = 'username';
            $errors['username'] = 'Username is required';
        } elseif (\preg_match('/^[a-zA-Z0-9._ -]*$/D', $values['username']) !== 1) {
            $errorsForMessage[] = 'username';
            $errors['username'] = 'Username is invalid';
        } elseif (!UserService::isUsernameAvailable($values['username'])) {
            $errorsForMessage[] = 'username';
            $errors['username'] = 'Username is unavailable';
        }

        // email
        $values['email'] = $params[$this->inputs['email']];
        if ($values['email'] === '') {
            $errorsForMessage[] = 'email';
            $errors['email'] = 'Email is required';
        } else {
            $posArobase = \mb_strpos($values['email'], '@');
            if ($posArobase < 1 || $posArobase === \mb_strlen($values['email']) - 1) {
                $errorsForMessage[] = 'email';
                $errors['email'] = 'Email is invalid';
            } elseif (!UserService::isEmailAvailable($values['email'])) {
                $errorsForMessage[] = 'email';
                $errors['email'] = 'Email is unavailable';
            }
        }

        // password
        $values['password'] = $params[$this->inputs['password']];
        $values['password_confirm'] = $params[$this->inputs['password_confirm']];
        $minLengthPassword = UserService::getMinLengthPassword();
        $errorLengthPassword = \sprintf('Password must be at least %d characters in length', $minLengthPassword);
        $pass1Len = \mb_strlen($values['password']);
        $pass2Len = \mb_strlen($values['password_confirm']);
        if ($pass1Len < $minLengthPassword && $pass2Len >= $minLengthPassword) {
            $errorsForMessage[] = 'password';
            $errors['password'] = $errorLengthPassword;
        } elseif ($pass1Len >= $minLengthPassword && $pass2Len < $minLengthPassword) {
            $errorsForMessage[] = 'password';
            $errors['password_confirm'] = $errorLengthPassword;
        } elseif ($pass1Len < $minLengthPassword && $pass2Len < $minLengthPassword) {
            $errorsForMessage[] = 'password';
            $errors['password'] = $errorLengthPassword;
            $errors['password_confirm'] = $errorLengthPassword;
        } elseif ($values['password'] !== $values['password_confirm']) {
            $errorsForMessage[] = 'password';
            $errors['password_confirm'] = 'Confirm Password must be the same as Password';
        } elseif (!UserService::isPasswordMatchFormat($values['password'])) {
            $errorsForMessage[] = 'password';
            $errors['password'] = 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-register', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-register-errors', $errors);
            Session::setFlash('form-register-values', $values);
            Session::keepFlash(['error-form-register', 'form-register-errors', 'form-register-values']);

            unset($values['password'], $values['password_confirm']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     *
     * @return ResponseInterface|null
     */
    protected function doProcessRegister(ServerRequestInterface $request, ?array $params): ResponseInterface
    {
        $baseUri = (new Factory())->createUri($request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        $uriError = (string) $baseUri->withFragment('popin-register');

        if ($params === null) {
            return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
        }

        $forceRollback = false;
        $errorCode = '#001';
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            [$userID, $errorCode] = UserService::createMemberUser($params['username'], $params['email'], $params['password']); // phpcs:ignore
            if ($userID === null) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because user requirements has been done before
                 * For covering we have to mock the database
                 */
                throw new \Exception('Error, could not create account (' . $errorCode . ')');
                // @codeCoverageIgnoreEnd
            }

            $emailSent = UserService::generateAndSendConfirmAccountEmail($userID, 'register');
            if ($emailSent === false) {
                $errorCode = '#500';
                throw new \Exception('Error, could not create account (' . $errorCode . ')');
            }
        } catch (\Exception $exception) {
            $forceRollback = true;
            unset($params['password'], $params['password_confirm']);

            Session::setFlash('error-form-register', 'Error, could not create account (' . $errorCode . ')');
            Session::setFlash('form-register-values', $params);
            Session::keepFlash(['error-form-register', 'form-register-values']);

            return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return (new Factory())->createResponse(301)->withHeader('Location', '/confirm-account/');
    }
}
