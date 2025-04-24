<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\ForgotPassword;

use app\helpers\Helper;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Crypt\CryptException;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class ForgotPasswordTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
    }

    /**
     * @throws DatabaseException
     * @throws CryptException
     */
    protected function setUp(): void
    {
        static::$db->truncateTables('users');

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token);
        SQL;

        $userParams = [
            'id'             => 20,
            'username'       => 'user_20',
            'hash'           => Crypt::hash('tgbrfvyhnuj'),
            'slug'           => 'user_20',
            'email'          => 'user_20@example.com',
            'grade'          => 'member',
            'remember_token' => 'remember_token_user_20'
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'             => static::$anonymousID,
            'username'       => 'user_anonymous',
            'hash'           => null,
            'slug'           => 'user_anonymous',
            'email'          => 'user_anonymous@example.com',
            'grade'          => 'member',
            'remember_token' => null
        ];
        static::$db->insert($sql, $userParams);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testForgotPasswordGETInvalidConfigurationEmail(): void
    {
        $response = $this->getResponseFromApplication('GET', '/', [], [], [], [], [], [], [], 'tests-invalid-mail-from-address.env');
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlBody($response, '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" role="alert">Error, could not use this form, "MAIL_FROM_ADDRESS" env variable is invalid.</div>');
    }

    /** @throws \Exception */
    protected static function getEmailHTML(): string
    {
        \ob_start();
        $ds = \DIRECTORY_SEPARATOR;
        require \dirname(__DIR__, 3) . $ds . 'app' . $ds . 'views/emails/forgot_password.html';

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
            'https://blueprintue.test/reset-password/?reset_token={{TOKEN}}',
            $now->format('Y'),
            Security::escHTML('user_20'),
            Security::escHTML('this_site_name'),
            Security::escAttr('this_site_name'),
            Security::escAttr('https://blueprintue.test/full-logo.png'),
        ];

        return \str_replace($search, $replace, $html);
    }

    protected static function getEmailText(): string
    {
        $text = 'You have received this email because a password reset request was received for the account user_20.' . "\n";
        $text .= 'Copy the URL below to complete the process:' . "\n\n";
        $text .= 'https://blueprintue.test/reset-password/?reset_token={{TOKEN}}' . "\n\n";
        $text .= 'If you did not request a password reset, no further action is required on your part.';

        return $text;
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCasesForgotPasswordPOST(): array
    {
        return [
            'forgot password OK + mail sent' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => 'user_20@example.com',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => static::getEmailText(),
                'mailHTML'           => static::getEmailHTML(),
                'mailSent'           => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'forgot password OK + mail sent after waiting +300' => [
                'sqlQueries' => [
                    "UPDATE users SET password_reset = 'aze', password_reset_at = utc_timestamp() - interval 6 minute WHERE id = 20"
                ],
                'params' => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => 'user_20@example.com',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => static::getEmailText(),
                'mailHTML'           => static::getEmailHTML(),
                'mailSent'           => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'forgot password OK + mail sent KO' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => 'user_20@example.com',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailText'           => static::getEmailText(),
                'mailHTML'           => static::getEmailHTML(),
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error, could not send email for reset password</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [],
            ],
            'csrf incorrect' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'incorrect_csrf',
                    'form-forgot_password-input-email' => 'user_20@example.com',
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'sqlQueries'            => [],
                'params'                => [],
                'useCsrfFromSession'    => false,
                'mailCalled'            => 0,
                'mailText'              => '',
                'mailHTML'              => '',
                'mailSent'              => false,
                'hasRedirection'        => false,
                'isFormSuccess'         => false,
                'flashMessages'         => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-input-email' => 'user_20@example.com',
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no email' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - email empty' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => ' ',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [
                    'email' => 'Email is required',
                ],
            ],
            'invalid fields - invalid email' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => ' - user-005 - ',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [
                    'email' => 'Email is invalid',
                ],
            ],
            'invalid fields - email not found' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => '0<script>alert("email");</script>@<script>alert("email");</script>',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error, could not reset password</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [],
            ],
            'invalid fields - anonymous user is not allowed to forgot password' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => 'user_anonymous@example.com',
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">Error, could not reset password</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - email' => [
                'sqlQueries' => [],
                'params'     => [
                    'form-forgot_password-hidden-csrf' => 'csrf_is_replaced',
                    'form-forgot_password-input-email' => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailText'           => '',
                'mailHTML'           => '',
                'mailSent'           => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-forgot_password" data-popin="forgot_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ]
        ];
    }

    /**
     * @dataProvider dataCasesForgotPasswordPOST
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesForgotPasswordPOST')]
    public function testForgotPasswordPOST(array $sqlQueries, array $params, bool $useCsrfFromSession, int $mailCalled, string $mailText, string $mailHTML, bool $mailSent, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        // set how mail must return in $_SESSION
        $session = [
            'remove' => [],
            'set'    => [
                'phpunit_mail_called' => 0,
                'phpunit_mail_html'   => $mailHTML,
                'phpunit_mail_text'   => $mailText,
                'phpunit_return'      => $mailSent,
            ],
        ];

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $session);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-forgot_password-hidden-csrf'] = $_SESSION['csrf'];
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/#popin-forgot_password', $params);

        $user20 = static::$db->selectRow('SELECT password_reset, password_reset_at FROM users WHERE id = 20');
        $userAnonymous = static::$db->selectRow('SELECT password_reset, password_reset_at FROM users WHERE id = ' . static::$anonymousID);

        if ($hasRedirection) {
            if ($isFormSuccess) {
                static::assertSame('/reset-password/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/#popin-forgot_password', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        static::assertNull($userAnonymous['password_reset']);
        static::assertNull($userAnonymous['password_reset_at']);

        if ($isFormSuccess) {
            static::assertNotNull($user20['password_reset']);
            static::assertNotNull($user20['password_reset_at']);
        } else {
            static::assertNull($user20['password_reset']);
            static::assertNull($user20['password_reset_at']);
        }

        static::assertSame($mailCalled, $_SESSION['phpunit_mail_called']);

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        if (!$isFormSuccess) {
            // test fields HTML
            $fields = ['email'];
            foreach ($fields as $field) {
                $hasError = \in_array($field, $fieldsHasError, true);
                $hasValue = \in_array($field, $fieldsHasValue, true);
                $labelError = $fieldsLabelError[$field] ?? '';

                if ($field === 'email') {
                    $value = $hasValue ? Helper::trim($params['form-forgot_password-input-email']) : '';
                    $this->doTestHtmlForm($response, '#popin-forgot_password', $this->getHTMLFieldEmail($value, $hasError, $labelError));
                }
            }
        }

        // user do forgot, cannot send mail twice
        if ($mailSent) {
            $response = $this->getResponseFromApplication('POST', '/#popin-forgot_password', $params);
            $this->doTestHasResponseWithStatusCode($response, 301);
            static::assertSame('/reset-password/', $response->getHeaderLine('Location'));

            $user20Again = static::$db->selectRow('SELECT password_reset, password_reset_at FROM users WHERE id = 20');

            static::assertSame($user20Again['password_reset'], $user20['password_reset']);
            static::assertSame($user20Again['password_reset_at'], $user20['password_reset_at']);

            static::assertSame($mailCalled, $_SESSION['phpunit_mail_called']);
        }
    }

    /** @throws SecurityException */
    protected function getHTMLFieldEmail(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-forgot_password-label-email form-forgot_password-label-email-error" aria-required="true" autocomplete="email" class="form__input form__input--invisible form__input--error" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-forgot_password-input-email" name="form-forgot_password-input-email" placeholder="your@email.com" type="text" value="$v"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-forgot_password-input-email" id="form-forgot_password-label-email-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-forgot_password-label-email" aria-required="true" autocomplete="email" class="form__input form__input--invisible" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-forgot_password-input-email" name="form-forgot_password-input-email" placeholder="your@email.com" type="text" value="$v"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    /** @throws DatabaseException */
    public static function mailForPHPUnit(string $to, string $subject, string $html, string $text, Database $db): bool
    {
        ++$_SESSION['phpunit_mail_called'];

        if ($_SESSION['phpunit_mail_html'] !== '') {
            $token = (string) $db->selectVar('SELECT password_reset FROM users WHERE id = 20');
            $_SESSION['phpunit_mail_html'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_html']);
            $_SESSION['phpunit_mail_text'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_text']);
        }

        static::assertSame('user_20@example.com', $to);
        static::assertSame('Reset password for this_site_name', $subject);
        static::assertSame($_SESSION['phpunit_mail_html'], $html);
        static::assertSame($_SESSION['phpunit_mail_text'], $text);

        return $_SESSION['phpunit_return'];
    }
}
