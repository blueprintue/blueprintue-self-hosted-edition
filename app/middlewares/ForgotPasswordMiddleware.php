<?php

declare(strict_types=1);

namespace app\middlewares;

use app\controllers\FormTrait;
use app\helpers\Helper;
use app\helpers\MailerHelper;
use app\services\www\UserService;
use DateTime;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

class ForgotPasswordMiddleware implements MiddlewareInterface
{
    use FormTrait;

    protected array $inputs = [
        'CSRF'     => 'form-forgot_password-hidden-csrf',
        'email'    => 'form-forgot_password-input-email',
    ];

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID')) {
            return $handler->handle($request);
        }

        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-forgot_password')) {
            $cleanedParams = $this->treatFormForgotPassword($request);

            return $this->doProcessForgotPassword($request, $cleanedParams);
        }

        return $handler->handle($request);
    }

    /** @throws \Exception */
    protected function treatFormForgotPassword(ServerRequestInterface $request): ?array
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
        } elseif (!$posArobase || ($posArobase < 1 || $posArobase === \mb_strlen($values['email']) - 1)) {
            $errorsForMessage[] = 'email';
            $errors['email'] = 'Email is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-forgot_password', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-forgot_password-errors', $errors);
            Session::setFlash('form-forgot_password-values', $values);
            Session::keepFlash(['error-form-forgot_password', 'form-forgot_password-errors', 'form-forgot_password-values']);

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
    protected function doProcessForgotPassword(ServerRequestInterface $request, ?array $params): ResponseInterface
    {
        $baseUri = (new Factory())->createUri($request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        $uriError = (string) $baseUri->withFragment('popin-forgot_password');

        if ($params === null) {
            return (new Factory())->createResponse(301)->withHeader('Location', $uriError);
        }

        $errorMessage = 'Error';
        $forceRollback = false;
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            [$token, $userFound, $username] = UserService::beginResetPasswordProcess($params['email']);
            if ($userFound === false) {
                $errorMessage = 'Error, could not reset password';
                throw new \Exception($errorMessage);
            }

            if ($token === null) {
                return (new Factory())->createResponse(301)->withHeader('Location', '/reset-password/');
            }

            $mailSent = $this->sendMail($params['email'], $token, $username);
            if ($mailSent === false) {
                $errorMessage = 'Error, could not send email for reset password';
                throw new \Exception($errorMessage);
            }
        } catch (\Exception $exception) {
            $forceRollback = true;

            Session::setFlash('error-form-forgot_password', $errorMessage);
            Session::setFlash('form-forgot_password-values', $params);
            Session::keepFlash(['error-form-forgot_password', 'form-forgot_password-values']);

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

        return (new Factory())->createResponse(301)->withHeader('Location', '/reset-password/');
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Security\SecurityException
     */
    protected function sendMail(string $email, string $token, string $username): bool
    {
        $subject = 'Reset password for ' . Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition');
        $html = $this->getEmailHTML($token, $username);
        $text = 'You have received this email because a password reset request was received for the account ' . $username . '.' . "\n";
        $text .= 'Copy the URL below to complete the process:' . "\n\n";
        $text .= Helper::getHostname() . Application::getRouter()->generateUrl('reset-password') . '?reset_token=' . $token . "\n\n";
        $text .= 'If you did not request a password reset, no further action is required on your part.';

        // only use for phpunit
        if (\function_exists('\tests\isPHPUnit')) {
            return \tests\www\ForgotPassword\ForgotPasswordTest::mailForPHPUnit($email, $subject, $html, $text, Application::getDatabase());
        }

        // @codeCoverageIgnoreStart
        /*
         * coverage is blocked by the function above
         */
        $mailer = new MailerHelper(true);
        $mailer->setHTMLEmail($subject, $html, $text);

        return $mailer->send($email);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Security\SecurityException
     * @throws \Exception
     */
    protected function getEmailHTML(string $token, string $username): string
    {
        $url = Helper::getHostname() . Application::getRouter()->generateUrl('reset-password') . '?reset_token=' . $token;
        \ob_start();
        require Application::getFolder('VIEWS') . 'emails/forgot_password.html';

        $html = \ob_get_clean();

        $now = new DateTime('now', new DateTimeZone('UTC'));

        $search = [
            '{{URL}}',
            '{{YEAR}}',
            '{{USERNAME}}',
            '{{SITE_NAME_HTML}}',
            '{{SITE_NAME_ATTR}}',
            '{{MAIL_HEADER_LOGO_PATH}}',
        ];

        $replace = [
            $url,
            $now->format('Y'),
            Security::escHTML($username),
            Security::escHTML(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')),
            Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')),
            Security::escAttr(Helper::getHostname() . '/' . Security::escAttr(Application::getConfig()->get('MAIL_HEADER_LOGO_PATH', 'blueprintue-self-hosted-edition_logo-full.png'))),
        ];

        return \str_replace($search, $replace, $html);
    }
}
