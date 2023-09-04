<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Helper;

use app\helpers\MailerHelper;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Environment\Environment;
use tests\Common;

class MailerTest extends TestCase
{
    use Common;

    /**
     * @return \string[][]
     */
    public function dataCases(): array
    {
        return [
            'mail text' => [
                'env_file'                    => 'mail.env',
                'use_custom_email_validation' => false,
                'construct_assertions'        => [
                    'Mailer'      => 'mail',
                    'Host'        => 'localhost',
                    'Port'        => 25,
                    'SMTPAutoTLS' => true,
                    'SMTPAuth'    => false,
                    'Username'    => '',
                    'Password'    => '',
                    'From'        => 'no-reply@blueprintue.test',
                    'FromName'    => 'blueprintUE_from_name',
                ],
                'content' => [
                    'type'    => 'text',
                    'subject' => 'my subject',
                    'message' => 'my message',
                ],
            ],
            'smtp html' => [
                'env_file'                    => 'mail_smtp.env',
                'use_custom_email_validation' => false,
                'construct_assertions'        => [
                    'Mailer'      => 'smtp',
                    'Host'        => 'smtp_host.com',
                    'Port'        => 72,
                    'SMTPAutoTLS' => false,
                    'SMTPAuth'    => false,
                    'Username'    => '',
                    'Password'    => '',
                    'From'        => 'no-reply@blueprintue.test',
                    'FromName'    => 'blueprintUE_from_name',
                ],
                'content' => [
                    'type'    => 'html',
                    'subject' => 'my subject',
                    'html'    => 'my message in html',
                    'text'    => 'my message in text',
                ],
            ],
            'smtp + auth' => [
                'env_file'                    => 'mail_smtp_auth.env',
                'use_custom_email_validation' => false,
                'construct_assertions'        => [
                    'Mailer'      => 'smtp',
                    'Host'        => 'smtp_auth_host.com',
                    'Port'        => 954,
                    'SMTPAutoTLS' => false,
                    'SMTPAuth'    => true,
                    'Username'    => 'the user',
                    'Password'    => 'the password',
                    'From'        => 'no-reply@blueprintue.test',
                    'FromName'    => 'blueprintUE_from_name',
                ],
                'content' => [
                    'type'    => 'text',
                    'subject' => 'my subject',
                    'message' => 'my message',
                ],
            ],
            'exception because subject and body are empty' => [
                'env_file'                    => 'mail.env',
                'use_custom_email_validation' => false,
                'construct_assertions'        => [
                    'Mailer'      => 'mail',
                    'Host'        => 'localhost',
                    'Port'        => 25,
                    'SMTPAutoTLS' => true,
                    'SMTPAuth'    => false,
                    'Username'    => '',
                    'Password'    => '',
                    'From'        => 'no-reply@blueprintue.test',
                    'FromName'    => 'blueprintUE_from_name',
                ],
                'content' => [
                    'type' => 'none',
                ],
            ],
            'invalid address' => [
                'env_file'                    => 'mail_invalid_address.env',
                'use_custom_email_validation' => false,
                'construct_assertions'        => [
                    'Mailer'      => 'mail',
                    'Host'        => 'localhost',
                    'Port'        => 25,
                    'SMTPAutoTLS' => true,
                    'SMTPAuth'    => false,
                    'Username'    => '',
                    'Password'    => '',
                    'From'        => '',
                    'FromName'    => '',
                ],
                'content' => [
                    'type'    => 'text',
                    'subject' => 'my subject',
                    'message' => 'my message',
                ],
            ],
            'use custom email validation' => [
                'env_file'                    => 'mail.env',
                'use_custom_email_validation' => true,
                'construct_assertions'        => [
                    'Mailer'      => 'mail',
                    'Host'        => 'localhost',
                    'Port'        => 25,
                    'SMTPAutoTLS' => true,
                    'SMTPAuth'    => false,
                    'Username'    => '',
                    'Password'    => '',
                    'From'        => 'no-reply@blueprintue.test',
                    'FromName'    => 'blueprintUE_from_name',
                ],
                'content' => [
                    'type'    => 'text',
                    'subject' => 'my subject',
                    'message' => 'my message',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCases
     *
     * @param string $envFile
     * @param bool   $useCustomEmailValidation
     * @param array  $constructAssertions
     * @param array  $content
     *
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function testMailer(string $envFile, bool $useCustomEmailValidation, array $constructAssertions, array $content): void
    {
        // setup app
        $ds = \DIRECTORY_SEPARATOR;
        $folders = [
            'ROOT'    => \dirname(__DIR__, 4),
            'ROUTES'  => \dirname(__DIR__, 4) . $ds . 'app' . $ds . 'routes',
            'VIEWS'   => \dirname(__DIR__, 4) . $ds . 'app' . $ds . 'views',
            'STORAGE' => \dirname(__DIR__, 4) . $ds . 'tests' . $ds . 'storage_test',
        ];

        $env = new Environment(__DIR__, $envFile);

        $_SERVER['HTTP_HOST'] = $env->get('HOST');
        $_SERVER['HTTPS'] = ($env->get('HTTPS') === true) ? 'on' : 'off';

        new Application($folders, $env);

        // setup mailer
        $mailer = new MailerHelper($useCustomEmailValidation);
        $reflection = new \ReflectionClass($mailer);
        $reflectionProperty = $reflection->getProperty('mailer');
        $reflectionProperty->setAccessible(true);

        // check construct
        $mailerProp = $reflectionProperty->getValue($mailer);
        foreach ($constructAssertions as $key => $value) {
            static::assertSame($value, $mailerProp->{$key});
        }

        if ($useCustomEmailValidation) {
            static::assertTrue(\call_user_func($mailerProp::$validator, 'a@a'));
            static::assertFalse(\call_user_func($mailerProp::$validator, "a\na"));
        } else {
            static::assertFalse($mailerProp->validateAddress('a@a'));
            static::assertFalse($mailerProp->validateAddress("a\na"));
        }

        // check mail
        if ($content['type'] === 'text') {
            $mailer->setTextEmail($content['subject'], $content['message']);
            $mailerProp = $reflectionProperty->getValue($mailer);
            static::assertSame($content['subject'], $mailerProp->Subject);
            static::assertSame($content['message'], $mailerProp->Body);
            static::assertSame('text/plain', $mailerProp->ContentType);
        } elseif ($content['type'] === 'html') {
            $mailer->setHTMLEmail($content['subject'], $content['html'], $content['text']);
            $mailerProp = $reflectionProperty->getValue($mailer);
            static::assertSame($content['subject'], $mailerProp->Subject);
            static::assertSame($content['html'], $mailerProp->Body);
            static::assertSame($content['text'], $mailerProp->AltBody);
            static::assertSame('text/html', $mailerProp->ContentType);
        } else {
            $this->expectException(\PHPMailer\PHPMailer\Exception::class);
            $this->expectExceptionMessage('Mail has empty subject and message');
        }

        // false address used is normal: avoid sending real email
        $mailer->send('***');
    }
}
