<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Login;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Crypt\CryptException;
use Rancoud\Database\Database;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class LoginTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`, `confirmed_sent_at`, `confirmed_at`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token, :confirmed_sent_at, :confirmed_at);
        SQL;

        $userParams = [
            'id'                => 5,
            'username'          => 'user_5',
            'hash'              => Crypt::hash('qsdfghjklm'),
            'slug'              => 'user_5',
            'email'             => 'user_5@example.com',
            'grade'             => 'member',
            'remember_token'    => 'XDYtGT691XiPWiUZSUGCt21zWF7svbnEbmqjrxDmdP1Wqs3fkIEuSu98iwSJcddVH8shXtaznY5UNbZlF8Qbyp6m4vxbKlY7GWBLL8V9wAPd4xr0yHfnlZokaPMKfJY3nQkrgHq3xxUkARPe8NhxgaHPpWw8F99rtSn9Tpalf0QiKIwsOG9T0S7ssNUtOENB1lPal2jW4kuqdnAS7Jvy19bYeJasy7koLOyrCo6aqt6UfuSgLI6ClhNVsAtKkm0',
            'confirmed_sent_at' => \gmdate('Y-m-d H:i:s'),
            'confirmed_at'      => \gmdate('Y-m-d H:i:s'),
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'                => static::$anonymousID,
            'username'          => 'user_anonymous',
            'hash'              => null,
            'slug'              => 'user_anonymous',
            'email'             => 'user_anonymous@example.com',
            'grade'             => 'member',
            'remember_token'    => null,
            'confirmed_sent_at' => \gmdate('Y-m-d H:i:s'),
            'confirmed_at'      => \gmdate('Y-m-d H:i:s'),
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'                => 75,
            'username'          => 'user_75',
            'hash'              => Crypt::hash('123123123'),
            'slug'              => 'user_75',
            'email'             => 'user_75@example.com',
            'grade'             => 'member',
            'remember_token'    => null,
            'confirmed_sent_at' => null,
            'confirmed_at'      => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'                => 85,
            'username'          => 'user_85',
            'hash'              => Crypt::hash('789789789'),
            'slug'              => 'user_85',
            'email'             => 'user_85@example.com',
            'grade'             => 'member',
            'remember_token'    => null,
            'confirmed_sent_at' => \gmdate('Y-m-d H:i:s'),
            'confirmed_at'      => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'                => 95,
            'username'          => 'user_95',
            'hash'              => Crypt::hash('456456456'),
            'slug'              => 'user_95',
            'email'             => 'user_95@example.com',
            'grade'             => 'member',
            'remember_token'    => null,
            'confirmed_sent_at' => (new DateTime('NOW', new DateTimeZone('UTC')))->modify('-6 minutes')->format('Y-m-d H:i:s'),
            'confirmed_at'      => null,
        ];
        static::$db->insert($sql, $userParams);
    }

    /**
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        static::$db->update('UPDATE users SET last_login_at = NULL');
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public function dataCasesLoginPOST(): array
    {
        return [
            'login OK' => [
                'user_id' => 5,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => true,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => true,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'login OK + add remember cookie' => [
                'user_id' => 5,
                'params'  => [
                    'form-login-hidden-csrf'       => 'csrf_is_replaced',
                    'form-login-input-username'    => 'user_5',
                    'form-login-input-password'    => 'qsdfghjklm',
                    'form-login-checkbox-remember' => 'remember'
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => true,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => true,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'NO login -> credentials OK but not confirmed - send email' => [
                'user_id' => 75,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_75',
                    'form-login-input-password'         => '123123123',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => true,
                'is_user_confirmed'     => false,
                'mail_called'           => 1,
                'mail_text'             => $this->getEmailTextConfirmAccount('user_75'),
                'mail_html'             => $this->getEmailHTMLConfirmAccount('user_75'),
                'mail_sent'             => true,
            ],
            'NO login -> credentials OK but not confirmed - email already sent' => [
                'user_id' => 85,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_85',
                    'form-login-input-password'         => '789789789',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => true,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'csrf incorrect' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'incorrect_csrf',
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => false,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'NO login -> credentials OK but not confirmed - email already sent but resend after 5 minutes' => [
                'user_id' => 95,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_95',
                    'form-login-input-password'         => '456456456',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => true,
                'is_user_confirmed'     => false,
                'mail_called'           => 1,
                'mail_text'             => $this->getEmailTextConfirmAccount('user_95'),
                'mail_html'             => $this->getEmailHTMLConfirmAccount('user_95'),
                'mail_sent'             => true,
            ],
            'missing fields - no fields' => [
                'user_id'               => null,
                'params'                => [],
                'use_csrf_from_session' => false,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'missing fields - no csrf' => [
                'user_id' => null,
                'params'  => [
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => false,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'missing fields - no username' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, missing fields</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'missing fields - no password' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_5',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, missing fields</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'empty fields - username empty' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => ' ',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'empty fields - password empty' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => ' ',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid credentials - invalid username' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => ' - user-005 - ',
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid credentials - invalid password' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => 'bad_password',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid credentials - anonymous user is not allowed to login (password is null in database)' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_anonymous',
                    'form-login-input-password'         => 'password_null',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid credentials - anonymous user is not allowed to login (password is not null in database)' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_anonymous',
                    'form-login-input-password'         => 'password_not_null',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => true,
                'flash_messages'        => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">Error, invalid credentials</div>'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid encoding fields - username' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => \chr(99999999),
                    'form-login-input-password'         => 'qsdfghjklm',
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
            'invalid encoding fields - password' => [
                'user_id' => null,
                'params'  => [
                    'form-login-hidden-csrf'            => 'csrf_is_replaced',
                    'form-login-input-username'         => 'user_5',
                    'form-login-input-password'         => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'is_user_logged'        => false,
                'has_redirection'       => false,
                'flash_messages'        => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-login" data-popin="login" role="alert">'
                    ]
                ],
                'redirect_for_confirm'  => false,
                'is_user_confirmed'     => false,
                'mail_called'           => 0,
                'mail_text'             => '',
                'mail_html'             => '',
                'mail_sent'             => false,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesLoginPOST
     *
     * @param int|null $userID
     * @param array    $params
     * @param bool     $useCsrfFromSession
     * @param bool     $isUserLogged
     * @param bool     $hasRedirection
     * @param array    $flashMessages
     * @param bool     $redirectForConfirm
     * @param bool     $isUserConfirmed
     * @param int      $mailCalled
     * @param string   $mailText
     * @param string   $mailHtml
     * @param bool     $mailSent
     *
     * @throws ApplicationException
     * @throws CryptException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testLoginPOST(?int $userID, array $params, bool $useCsrfFromSession, bool $isUserLogged, bool $hasRedirection, array $flashMessages, bool $redirectForConfirm, bool $isUserConfirmed, int $mailCalled, string $mailText, string $mailHtml, bool $mailSent): void
    {
        $sessionValues = [
            'set' => [
                'phpunit_mail_called' => 0,
                'phpunit_id_user'     => $userID,
                'phpunit_mail_html'   => $mailHtml,
                'phpunit_mail_text'   => $mailText,
                'phpunit_mail_sent'   => $mailSent,
            ],
            'remove' => ['userID', 'username', 'grade', 'slug']
        ];
        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-login-hidden-csrf'] = $_SESSION['csrf'];
        }

        // hack for test anonymous and password not null
        if (isset($params['form-login-input-password']) && $params['form-login-input-password'] === 'password_not_null') {
            $hash = Crypt::hash($params['form-login-input-password']);
            static::$db->update('UPDATE users set password = :hash WHERE id = :userID', ['hash' => $hash, 'userID' => static::$anonymousID]);
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/#popin-login', $params);

        if ($hasRedirection) {
            if ($isUserLogged) {
                if (isset($params['form-login-checkbox-remember'])) {
                    static::assertNotNull(static::$db->selectVar('SELECT remember_token FROM users WHERE id = ' . $userID));
                } else {
                    static::assertNull(static::$db->selectVar('SELECT remember_token FROM users WHERE id = ' . $userID));
                }

                static::assertSame('/#login-success', $response->getHeaderLine('Location'));
            } elseif ($redirectForConfirm) {
                static::assertSame('/confirm-account/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/#popin-login', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        if ($isUserLogged) {
            static::assertArrayHasKey('userID', $_SESSION);
            static::assertSame($userID, $_SESSION['userID']);
            static::assertSame('user_' . $userID, $_SESSION['username']);
            static::assertSame('member', $_SESSION['grade']);
            static::assertSame('user_' . $userID, $_SESSION['slug']);

            static::assertNotNull(static::$db->selectVar('SELECT last_login_at FROM users WHERE id = :user_id', ['user_id' => $userID]));
        } else {
            static::assertArrayNotHasKey('userID', $_SESSION);
            static::assertSame(5, (int) static::$db->selectCol('SELECT COUNT(id) FROM users WHERE last_login_at IS NULL')[0]);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        // user logged, cannot logged twice
        if ($isUserLogged) {
            $response = $this->getResponseFromApplication('POST', '/', $params);
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        if ($isUserConfirmed === false) {
            static::assertArrayNotHasKey('userID', $_SESSION);
        }

        static::assertSame($mailCalled, $_SESSION['phpunit_mail_called']);
    }

    /**
     * @param string   $to
     * @param string   $subject
     * @param string   $html
     * @param string   $text
     * @param string   $token
     * @param Database $db
     *
     * @throws DatabaseException
     *
     * @return bool
     */
    public static function mailForPHPUnit(string $to, string $subject, string $html, string $text, string $token, Database $db): bool
    {
        ++$_SESSION['phpunit_mail_called'];

        if ($_SESSION['phpunit_mail_text'] !== '') {
            $_SESSION['phpunit_mail_text'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_text']);
            $_SESSION['phpunit_mail_html'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_html']);

            $email = $db->selectVar('SELECT email FROM users WHERE id = :userID', ['userID' => $_SESSION['phpunit_id_user']]);
            static::assertSame($email, $to);
        }

        static::assertSame('Confirm your account for this_site_name', $subject);
        static::assertSame($_SESSION['phpunit_mail_text'], $text);
        static::assertSame($_SESSION['phpunit_mail_html'], $html);

        return $_SESSION['phpunit_mail_sent'];
    }
}
