<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Session\Session;

class ResetPasswordController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected array $inputs = [
        'CSRF'             => 'form-reset_password-hidden-csrf',
        'email'            => 'form-reset_password-input-email',
        'password'         => 'form-reset_password-input-password',
        'password_confirm' => 'form-reset_password-input-password_confirm'
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'reset_password';
        $this->currentPageForNavBar = 'reset-password';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('reset-password') ?? '';

        $this->title = 'Reset Password | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $this->description = 'Reset Password';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID')) {
            return $this->redirect('/');
        }

        $hasResetToken = !empty($request->getQueryParams()['reset_token']);

        if ($hasResetToken && $this->hasSentForm($request, 'POST', $this->inputs, 'error-form-reset_password')) {
            $cleanedParams = $this->treatFormResetPassword($request);
            $this->doProcessResetPassword($cleanedParams);

            return $this->redirect(Application::getRouter()->generateUrl('reset-password') . '?reset_token=' . $request->getQueryParams()['reset_token']);
        }

        $this->setTemplateProperties();

        $this->data += [$this->inputs['CSRF'] => Session::get('csrf')];
        $this->data += ['has_reset_token' => $hasResetToken];
        if ($hasResetToken) {
            $this->data += ['reset_token' => $request->getQueryParams()['reset_token']];
        } else {
            $this->data += ['reset_token' => ''];
        }

        $formResetPassword = new FormHelper();
        $formResetPassword->setInputsValues(Session::getFlash('form-reset_password-values'));
        $formResetPassword->setInputsErrors(Session::getFlash('form-reset_password-errors'));
        $formResetPassword->setErrorMessage(Session::getFlash('error-form-reset_password'));
        $formResetPassword->setSuccessMessage(Session::getFlash('success-form-reset_password'));
        $this->data += ['form-reset_password' => $formResetPassword];

        return $this->sendPage();
    }

    /**
     * @throws \Exception
     */
    protected function treatFormResetPassword(ServerRequestInterface $request): ?array
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

        // email
        $values['email'] = $params[$this->inputs['email']];
        $posArobase = \mb_strpos($values['email'], '@');
        if ($values['email'] === '') {
            $errorsForMessage[] = 'email';
            $errors['email'] = 'Email is required';
        } elseif ($posArobase < 1 || $posArobase === \mb_strlen($values['email']) - 1) {
            $errorsForMessage[] = 'email';
            $errors['email'] = 'Email is invalid';
        }

        // token
        $values['token'] = Helper::trim($request->getQueryParams()['reset_token']);

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
            $errors['password_confirm'] = 'Confirm New Password must be the same as New Password';
        } elseif (!UserService::isPasswordMatchFormat($values['password'])) {
            $errorsForMessage[] = 'password';
            $errors['password'] = 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-reset_password', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-reset_password-errors', $errors);
            Session::setFlash('form-reset_password-values', $values);
            Session::keepFlash(['error-form-reset_password', 'form-reset_password-errors', 'form-reset_password-values']);

            unset($values['password'], $values['password_confirm']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    protected function doProcessResetPassword(?array $params): void
    {
        if ($params === null) {
            return;
        }

        $userID = UserService::findUserIDFromEmailAndToken($params['email'], $params['token']);
        if ($userID === null || $userID === (int) Application::getConfig()->get('ANONYMOUS_ID')) {
            unset($params['password'], $params['password_confirm']);

            Session::setFlash('error-form-reset_password', 'Error, email and/or reset token are invalid');
            Session::setFlash('form-reset_password-values', $params);
            Session::keepFlash(['error-form-reset_password', 'form-reset_password-values']);

            return;
        }

        try {
            UserService::resetPassword($userID, $params['password']);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because user requirements has been done before
             * For covering we have to mock the database
             */
            unset($params['password'], $params['password_confirm']);

            Session::setFlash('error-form-reset_password', 'Error, could not reset password');
            Session::setFlash('form-reset_password-values', $params);
            Session::keepFlash(['error-form-reset_password', 'form-reset_password-values']);

            return;
            // @codeCoverageIgnoreEnd
        }

        Session::setFlash('success-form-reset_password', 'Your new password has been saved successfully');
        Session::keepFlash(['success-form-reset_password']);
    }
}
