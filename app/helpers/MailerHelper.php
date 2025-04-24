<?php

declare(strict_types=1);

namespace app\helpers;

use PHPMailer\PHPMailer\PHPMailer;
use Rancoud\Application\Application;

class MailerHelper
{
    protected ?PHPMailer $mailer = null;

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function __construct(bool $useCustomEmailValidation = false)
    {
        $this->mailer = new PHPMailer();

        if ((bool) Application::getConfig()->get('MAIL_USE_SMTP', false) === true) {
            $this->mailer->isSMTP();
            $this->mailer->Host = (string) Application::getConfig()->get('MAIL_SMTP_HOST', 'localhost');
            $this->mailer->Port = (int) Application::getConfig()->get('MAIL_SMTP_PORT', 25);
            $this->mailer->SMTPAutoTLS = false;

            if ((bool) Application::getConfig()->get('MAIL_USE_SMTP_AUTH', false) === true) {
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = (string) Application::getConfig()->get('MAIL_SMTP_USER');
                $this->mailer->Password = (string) Application::getConfig()->get('MAIL_SMTP_PASSWORD');
            }

            if ((bool) Application::getConfig()->get('MAIL_USE_SMTP_TLS', false) === true) {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
        }

        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $mailAddress = (string) Application::getConfig()->get('MAIL_FROM_ADDRESS');
        $mailName = (string) Application::getConfig()->get('MAIL_FROM_NAME', '');
        $this->mailer->setFrom($mailAddress, $mailName);

        if ($useCustomEmailValidation) {
            /* @noinspection PhpUndefinedVariableInspection */
            $this->mailer::$validator = static function ($address) {
                if (\mb_strpos($address, "\n") !== false || \mb_strpos($address, "\r") !== false) {
                    return false;
                }

                return \mb_strpos($address, '@') !== false;
            };
        }
    }

    public function setTextEmail(string $subject, string $message): void
    {
        $this->mailer->isHTML(false);

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;
    }

    public function setHTMLEmail(string $subject, string $html, string $text): void
    {
        $this->mailer->isHTML(true);

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $html;
        $this->mailer->AltBody = $text;
    }

    /** @throws \PHPMailer\PHPMailer\Exception */
    public function send(string $to): bool
    {
        if ($this->mailer->Subject === '' && $this->mailer->Body === '') {
            throw new \PHPMailer\PHPMailer\Exception('Mail has empty subject and message');
        }

        $this->mailer->addAddress($to);

        return $this->mailer->send();
    }
}
