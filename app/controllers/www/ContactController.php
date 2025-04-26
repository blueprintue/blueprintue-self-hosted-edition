<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\helpers\MailerHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Session\Session;

class ContactController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected array $inputs = [
        'CSRF'     => 'form-contact-hidden-csrf',
        'name'     => 'form-contact-input-name',
        'email'    => 'form-contact-input-email',
        'message'  => 'form-contact-textarea-message'
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'contact';
        $this->currentPageForNavBar = 'contact';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('contact');

        $this->title = 'Contact us | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');
        $this->description = 'Contact us';
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $to = (string) Application::getConfig()->get('MAIL_CONTACT_TO');
        $posArobase = \mb_strpos($to, '@');
        if (!$posArobase || ($posArobase < 1 || $posArobase === \mb_strlen($to) - 1)) {
            $this->setTemplateProperties();

            $this->data += [$this->inputs['CSRF'] => Session::get('csrf')];

            $formContact = new FormHelper();
            $formContact->setErrorMessage('Error, could not use this form, "MAIL_CONTACT_TO" env variable is invalid.');
            $this->data += ['form-contact' => $formContact];

            return $this->sendPage();
        }

        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-contact')) {
            $cleanedParams = $this->treatFormContact($request);
            $this->doProcessContact($cleanedParams);

            return $this->redirect(Application::getRouter()->generateUrl('contact'));
        }

        $this->setTemplateProperties();

        $this->data += [$this->inputs['CSRF'] => Session::get('csrf')];

        $formContact = new FormHelper();
        $formContact->setInputsValues(Session::getFlash('form-contact-values'));
        $formContact->setInputsErrors(Session::getFlash('form-contact-errors'));
        $formContact->setErrorMessage(Session::getFlash('error-form-contact'));
        $formContact->setSuccessMessage(Session::getFlash('success-form-contact'));
        $this->data += ['form-contact' => $formContact];

        return $this->sendPage();
    }

    /** @throws \Exception */
    protected function treatFormContact(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = \mb_trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // name
        $values['name'] = $params[$this->inputs['name']];
        if ($values['name'] === '') {
            $errors['name'] = 'Name is required';
        }

        // email
        $values['email'] = $params[$this->inputs['email']];
        $posArobase = \mb_strpos($values['email'], '@');
        if ($values['email'] === '') {
            $errors['email'] = 'Email is required';
        } elseif (!$posArobase || ($posArobase < 1 || $posArobase === \mb_strlen($values['email']) - 1)) {
            $errors['email'] = 'Email is invalid';
        }

        // message
        $values['message'] = $params[$this->inputs['message']];
        if ($values['message'] === '') {
            $errors['message'] = 'Message is required';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-contact', 'Error, fields are invalid or required');
            Session::setFlash('form-contact-errors', $errors);
            Session::setFlash('form-contact-values', $values);
            Session::keepFlash(['error-form-contact', 'form-contact-errors', 'form-contact-values']);

            return null;
        }

        return $values;
    }

    /** @throws \Exception */
    protected function doProcessContact(?array $params): void
    {
        if ($params === null) {
            return;
        }

        $this->sendMail($params);
    }

    /** @throws \Exception */
    protected function sendMail(array $params): void
    {
        $to = (string) Application::getConfig()->get('MAIL_CONTACT_TO');

        $subject = 'Contact From ' . Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition');

        $emailMessage = 'Name: ' . $params['name'] . "\n";
        $emailMessage .= 'Email: ' . $params['email'] . "\n";
        $emailMessage .= 'Message: ' . $params['message'];

        // only use for phpunit
        if (\function_exists('\tests\isPHPUnit')) {
            if (\tests\www\Contact\ContactTest::mailForPHPUnit($to, $subject, $emailMessage)) {
                $this->setAndKeepInfos('success-form-contact', 'Message sent successfully');
            } else {
                $this->setAndKeepInfos('error-form-contact', 'Error, could not sent message, try later');
            }

            return;
        }

        // @codeCoverageIgnoreStart
        /*
         * coverage is blocked by the function above
         */
        $mailer = new MailerHelper();
        $mailer->setTextEmail($subject, $emailMessage);

        if ($mailer->send($to)) {
            $this->setAndKeepInfos('success-form-contact', 'Message sent successfully');
        } else {
            $this->setAndKeepInfos('error-form-contact', 'Error, could not sent message, try later');
        }
        // @codeCoverageIgnoreEnd
    }
}
