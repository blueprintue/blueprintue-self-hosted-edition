<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Register;

use app\helpers\Helper;
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

class RegisterTest extends TestCase
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
        static::$db->truncateTables('users_infos');

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`username`, `password`, `slug`, `email`, `grade`, `created_at`)
                VALUES (:username, :hash, :slug, :email, :grade, UTC_TIMESTAMP());
        SQL;

        $userParams = [
            'username' => 'user_1',
            'hash'     => Crypt::hash('azertyuiop'),
            'slug'     => 'user_1',
            'email'    => 'user_1@example.com',
            'grade'    => 'member'
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
    public function testRegisterGETInvalidConfigurationEmail(): void
    {
        $response = $this->getResponseFromApplication('GET', '/', [], [], [], [], [], [], [], 'tests-invalid-mail-from-address.env');
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlBody($response, '<div class="block__info block__info--error" data-flash-error-for="form-register" role="alert">Error, could not use this form, "MAIL_FROM_ADDRESS" env variable is invalid.</div>');
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCasesRegister(): array
    {
        return [
            'xss - register OK - mail OK' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => 'a-zA-Z0-9._ -',
                    'form-register-input-email'            => '0<script>alert("email");</script>@<script>alert("email");</script>',
                    'form-register-input-password'         => '0<script>alert("Password");</script>',
                    'form-register-input-password_confirm' => '0<script>alert("Password");</script>'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailHTML'           => static::getEmailHTMLConfirmAccount('a-zA-Z0-9._ -'),
                'mailText'           => static::getEmailTextConfirmAccount('a-zA-Z0-9._ -'),
                'mailSent'           => true,
                'isUserCreated'      => true,
                'userDB'             => [
                    'username' => 'a-zA-Z0-9._ -',
                    'slug'     => 'a-za-z0-9-_',
                    'email'    => '0<script>alert("email");</script>@<script>alert("email");</script>'
                ],
                'hasRedirection' => true,
                'flashMessages'  => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'xss - register KO' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => '0<script>alert("name");</script>',
                    'form-register-input-email'            => '0<script>alert("email");</script><script>alert("email");</script>',
                    'form-register-input-password'         => '0<script>alert("password");</script>',
                    'form-register-input-password_confirm' => '0<script>alert("password_confirm");</script>'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on username, email, password</div>'
                    ]
                ],
                'fieldsHasError'   => ['username', 'email', 'password_confirm'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'username'         => 'Username is invalid',
                    'email'            => 'Email is invalid',
                    'password_confirm' => 'Confirm Password must be the same as Password'
                ],
            ],
            'register OK - mail OK' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailHTML'           => static::getEmailHTMLConfirmAccount('- user-001 -'),
                'mailText'           => static::getEmailTextConfirmAccount('- user-001 -'),
                'mailSent'           => true,
                'isUserCreated'      => true,
                'userDB'             => [
                    'username' => '- user-001 -',
                    'slug'     => 'user-001',
                    'email'    => 'user@example.com'
                ],
                'hasRedirection' => true,
                'flashMessages'  => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'register OK - mail KO' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 1,
                'mailHTML'           => static::getEmailHTMLConfirmAccount('- user-001 -'),
                'mailText'           => static::getEmailTextConfirmAccount('- user-001 -'),
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => [
                    'username' => '- user-001 -',
                    'slug'     => 'user-001',
                    'email'    => 'user@example.com'
                ],
                'hasRedirection' => true,
                'flashMessages'  => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error, could not create account (#500)</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [],
            ],
            'csrf incorrect' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'incorrect_csrf',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'params'             => [],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'params' => [
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => false,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no username' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no email' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no password' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no password_confirm' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - username empty' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on username</div>'
                    ]
                ],
                'fieldsHasError'   => ['username'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'username' => 'Username is required'
                ],
            ],
            'empty fields - email empty' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => ' ',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'email' => 'Email is required'
                ],
            ],
            'empty fields - password empty' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => ' ',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must be at least 10 characters in length'
                ],
            ],
            'empty fields - password confirm' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => ' '
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - username invalid chars' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user/-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on username</div>'
                    ]
                ],
                'fieldsHasError'   => ['username'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'username' => 'Username is invalid'
                ],
            ],
            'invalid fields - username already used' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => 'user_1',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on username</div>'
                    ]
                ],
                'fieldsHasError'   => ['username'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'username' => 'Username is unavailable'
                ],
            ],
            'invalid fields - username already used (slug collide)' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => '-user_1-',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on username</div>'
                    ]
                ],
                'fieldsHasError'   => ['username'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'username' => 'Username is unavailable'
                ],
            ],
            'invalid fields - email invalid' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'userexample.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'email' => 'Email is invalid'
                ],
            ],
            'invalid fields - email already used' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user_1@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'email' => 'Email is unavailable'
                ],
            ],
            'invalid fields - password incorrect length' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'my',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password incorrect format (miss lowercase)' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => '_*_123RTYY',
                    'form-register-input-password_confirm' => '_*_123RTYY'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss uppercase)' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'aaze123_*_',
                    'form-register-input-password_confirm' => 'aaze123_*_'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss digit)' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'aaze_*_RTY',
                    'form-register-input-password_confirm' => 'aaze_*_RTY'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss special characters)' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'aaze123RTY',
                    'form-register-input-password_confirm' => 'aaze123RTY'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password_confirm incorrect length' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'my'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password and password_confirm incorrect length' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'my',
                    'form-register-input-password_confirm' => 'my'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password', 'password_confirm'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password'         => 'Password must be at least 10 characters in length',
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password and password_confirm different' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password02$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => true,
                'flashMessages'      => [
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['username', 'email'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Confirm Password must be the same as Password'
                ],
            ],
            'invalid encoding fields - username' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => \chr(99999999),
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - email' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => \chr(99999999),
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - password' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => \chr(99999999),
                    'form-register-input-password_confirm' => 'My_password01$'
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - password_confirm' => [
                'params' => [
                    'form-register-hidden-csrf'            => 'csrf_is_replaced',
                    'form-register-input-username'         => ' - user-001 - ',
                    'form-register-input-email'            => 'user@example.com',
                    'form-register-input-password'         => 'My_password01$',
                    'form-register-input-password_confirm' => \chr(99999999)
                ],
                'useCsrfFromSession' => true,
                'mailCalled'         => 0,
                'mailHTML'           => '',
                'mailText'           => '',
                'mailSent'           => false,
                'isUserCreated'      => false,
                'userDB'             => null,
                'hasRedirection'     => false,
                'flashMessages'      => [
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-register" data-popin="register" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesRegister
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesRegister')]
    public function testRegisterPOST(array $params, bool $useCsrfFromSession, int $mailCalled, string $mailHTML, string $mailText, bool $mailSent, bool $isUserCreated, ?array $userDB, bool $hasRedirection, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        $sessionValues = [
            'set' => [
                'phpunit_mail_called' => 0,
                'phpunit_id_user'     => 2,
                'phpunit_mail_html'   => $mailHTML,
                'phpunit_mail_text'   => $mailText,
                'phpunit_return'      => $mailSent,
            ],
            'remove' => ['userID', 'username', 'grade', 'slug']
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-register-hidden-csrf'] = $_SESSION['csrf'];
        }

        $countUsers = static::$db->count('SELECT COUNT(*) FROM users');

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/#popin-register', $params);

        if ($hasRedirection) {
            if ($isUserCreated) {
                static::assertSame('/confirm-account/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/#popin-register', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        if ($isUserCreated) {
            static::assertSame($countUsers + 1, static::$db->count('SELECT COUNT(*) FROM users'));
            $userID = (int) static::$db->selectVar('SELECT id FROM users WHERE username = :username AND email = :email AND slug = :slug', $userDB);
            static::assertNotSame(0, $userID);
            static::assertSame(1, static::$db->count('SELECT COUNT(*) FROM users_infos WHERE id_user = :userID', ['userID' => $userID]));
            static::assertNotSame($params['form-register-input-password'], static::$db->selectVar('SELECT password FROM users WHERE id = :userID', ['userID' => $userID]));
        } else {
            static::assertSame($countUsers, static::$db->count('SELECT COUNT(*) FROM users'));
        }

        // register O or not OK -> no login
        static::assertArrayNotHasKey('userID', $_SESSION);

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        static::assertSame($mailCalled, $_SESSION['phpunit_mail_called']);

        if ($isUserCreated) {
            $this->doTestHtmlMainNot($response, '#popin-register');

            return;
        }

        // test fields HTML
        $fields = ['username', 'email', 'password', 'password_confirm'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'username') {
                $value = $hasValue ? Helper::trim($params['form-register-input-username']) : '';
                $this->doTestHtmlForm($response, '#popin-register', $this->getHTMLFieldUsername($value, $hasError, $labelError));
            }

            if ($field === 'email') {
                $value = $hasValue ? Helper::trim($params['form-register-input-email']) : '';
                $this->doTestHtmlForm($response, '#popin-register', $this->getHTMLFieldEmail($value, $hasError, $labelError));
            }

            if ($field === 'password') {
                $this->doTestHtmlForm($response, '#popin-register', $this->getHTMLFieldPassword($hasError, $labelError));
            }

            if ($field === 'password_confirm') {
                $this->doTestHtmlForm($response, '#popin-register', $this->getHTMLFieldPasswordConfirm($hasError, $labelError));
            }
        }
    }

    /** @throws SecurityException */
    protected function getHTMLFieldUsername(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-register-label-username form-register-label-username-error" aria-required="true" autocomplete="username" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-register-input-username" name="form-register-input-username" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-register-input-username" id="form-register-label-username-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-register-label-username" aria-required="true" autocomplete="username" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-register-input-username" name="form-register-input-username" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldEmail(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-register-label-email form-register-label-email-error" aria-required="true" autocomplete="email" class="form__input form__input--invisible form__input--error" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-register-input-email" name="form-register-input-email" type="text" value="{$v}"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-register-input-email" id="form-register-label-email-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-register-label-email" aria-required="true" autocomplete="email" class="form__input form__input--invisible" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-register-input-email" name="form-register-input-email" type="text" value="{$v}"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    protected function getHTMLFieldPassword(bool $hasError, string $labelError): string
    {
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-describedby="form-register-span-password" aria-invalid="false" aria-labelledby="form-register-label-password form-register-label-password-error" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible form__input--error" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-register-input-password" name="form-register-input-password" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-register-input-password" id="form-register-label-password-error">{$labelError}</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-describedby="form-register-span-password" aria-invalid="false" aria-labelledby="form-register-label-password" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-register-input-password" name="form-register-input-password" type="password"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    protected function getHTMLFieldPasswordConfirm(bool $hasError, string $labelError): string
    {
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-register-label-password_confirm form-register-label-password_confirm-error" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible form__input--error" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-register-input-password" id="form-register-input-password_confirm" name="form-register-input-password_confirm" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-register-input-password_confirm" id="form-register-label-password_confirm-error">{$labelError}</label>
HTML;
        }

        return <<<'HTML'
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-register-label-password_confirm" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-register-input-password" id="form-register-input-password_confirm" name="form-register-input-password_confirm" type="password"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    /** @throws DatabaseException */
    public static function mailForPHPUnit(string $to, string $subject, string $html, string $text, string $token, Database $db): bool
    {
        ++$_SESSION['phpunit_mail_called'];

        if ($_SESSION['phpunit_mail_html'] !== '') {
            $_SESSION['phpunit_mail_html'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_html']);
            $_SESSION['phpunit_mail_text'] = \str_replace('{{TOKEN}}', $token, $_SESSION['phpunit_mail_text']);

            $email = $db->selectVar('SELECT email FROM users WHERE id = :userID', ['userID' => $_SESSION['phpunit_id_user']]);
            static::assertSame($email, $to);
        }

        static::assertSame('Confirm your account for this_site_name', $subject);
        static::assertSame($_SESSION['phpunit_mail_html'], $html);
        static::assertSame($_SESSION['phpunit_mail_text'], $text);

        return $_SESSION['phpunit_return'];
    }
}
