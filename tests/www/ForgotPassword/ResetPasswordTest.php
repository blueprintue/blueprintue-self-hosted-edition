<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\ForgotPassword;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Crypt\CryptException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class ResetPasswordTest extends TestCase
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
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `password_reset`, `password_reset_at`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :password_reset, UTC_TIMESTAMP());
        SQL;

        $userParams = [
            'id'             => 30,
            'username'       => 'user_30',
            'hash'           => Crypt::hash('azertyuiop'),
            'slug'           => 'user_30',
            'email'          => 'user_30@example.com',
            'grade'          => 'member',
            'password_reset' => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr'
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
    public function testResetPasswordGET(): void
    {
        $response = $this->getResponseFromApplication('GET', '/reset-password/', [], ['set' => ['userID' => 1], 'remove' => []]);
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => 'Reset Password | This is a base title',
            'description' => 'Reset&#x20;Password'
        ]);
        $this->doTestHtmlBody($response, '<h2 class="block__title">Reset password</h2>');
        $this->doTestNavBarIsLogoOnly($response);

        $this->doTestHtmlMain($response, <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Reset password</h2>
<h3 class="block__subtitle">If you have requested a password reset then you have to check your emails.</h3>
</div>
</div>
HTML);

        $response = $this->getResponseFromApplication('GET', '/reset-password/', [], ['set' => [], 'remove' => ['userID']]);
        $this->doTestHasResponseWithStatusCode($response, 301);

        $response = $this->getResponseFromApplication('GET', '/reset-password/', [], [], [], ['reset_token' => 'lambda']);
        $this->doTestHtmlMain($response, <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Reset password</h2>


<form action="/reset-password/?reset_token=lambda" data-form-speak-error="Form is invalid:" id="form-reset_password" method="post">
HTML);
    }

    public static function dataCasesResetPasswordPOST(): array
    {
        return [
            'reset password OK' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'reset password KO - invalid email' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error, email and&#47;or reset token are invalid</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [],
            ],
            'reset password KO - invalid token' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'token',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error, email and&#47;or reset token are invalid</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['email'],
                'fieldsLabelError' => [],
            ],
            'xss - reset password KO' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => '0<script>alert("email");</script><script>alert("email");</script>',
                    'form-reset_password-input-password'         => '0<script>alert("password");</script>',
                    'form-reset_password-input-password_confirm' => '0<script>alert("password_confirm");</script>'
                ],
                'resetToken'         => '0<script>alert("reset_token");</script>',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on email, password</div>'
                    ]
                ],
                'fieldsHasError'   => ['email', 'password_confirm'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'email'            => 'Email is invalid',
                    'password_confirm' => 'Confirm New Password must be the same as New Password'
                ],
            ],
            'csrf incorrect' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'incorrect_csrf',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'params'             => [],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'params' => [
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no email' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no password' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no password_confirm' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - email empty' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => ' ',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'email' => 'Email is required'
                ],
            ],
            'empty fields - password empty' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => ' ',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must be at least 10 characters in length'
                ],
            ],
            'empty fields - password confirm' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => ' '
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - email invalid' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on email</div>'
                    ]
                ],
                'fieldsHasError'   => ['email'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'email' => 'Email is invalid'
                ],
            ],
            'invalid fields - password incorrect length' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'my',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password incorrect format (miss lowercase)' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => '_*_123RTYY',
                    'form-reset_password-input-password_confirm' => '_*_123RTYY'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss uppercase)' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'aaze123_*_',
                    'form-reset_password-input-password_confirm' => 'aaze123_*_'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss digit)' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'aaze_*_RTY',
                    'form-reset_password-input-password_confirm' => 'aaze_*_RTY'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password incorrect format (miss special characters)' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'aaze123RTY',
                    'form-reset_password-input-password_confirm' => 'aaze123RTY'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters'
                ],
            ],
            'invalid fields - password_confirm incorrect length' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'my'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password and password_confirm incorrect length' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'my',
                    'form-reset_password-input-password_confirm' => 'my'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password', 'password_confirm'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password'         => 'Password must be at least 10 characters in length',
                    'password_confirm' => 'Password must be at least 10 characters in length'
                ],
            ],
            'invalid fields - password and password_confirm different' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'my_password_01',
                    'form-reset_password-input-password_confirm' => 'my_password_02'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">Your new password has been saved successfully</div>'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">Error(s) on password</div>'
                    ]
                ],
                'fieldsHasError'   => ['password_confirm'],
                'fieldsHasValue'   => ['email', 'token'],
                'fieldsLabelError' => [
                    'password_confirm' => 'Confirm New Password must be the same as New Password'
                ],
            ],
            'invalid encoding fields - email' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => \chr(99999999),
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - password' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => \chr(99999999),
                    'form-reset_password-input-password_confirm' => 'My_password01$'
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - password_confirm' => [
                'params' => [
                    'form-reset_password-hidden-csrf'            => 'csrf_is_replaced',
                    'form-reset_password-input-email'            => 'user_30@example.com',
                    'form-reset_password-input-password'         => 'My_password01$',
                    'form-reset_password-input-password_confirm' => \chr(99999999)
                ],
                'resetToken'         => 'CuTRnFaXfbJQ3gnw9e6835D6iV3irDhLL8Fv5CXM4D98dT55Eh8Ug76zk795s34p33isfjbq3N92m23R6BP9v38wEJ8J47G8U6Wu4D4eZs8w8WC82Sb7ui5TMdq7CPqnN8VJ5Nrsr2R6Ebe8g78MbYXfxbNm46DwWT24hMvLp9SFS6x9LSc7984a2sar5XpT4iPxvnuNVMNK6BZMPWp5zdWN7pLQLc3r8V5h656eB2mtBW6srMr3MA3933Ptdfr',
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-reset_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-reset_password" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesResetPasswordPOST
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesResetPasswordPOST')]
    public function testResetPasswordPOST(array $params, ?string $resetToken, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        $queryParams = [];
        if ($resetToken !== null) {
            $queryParams = ['reset_token' => $resetToken];
        }

        // generate csrf
        $this->getResponseFromApplication('GET', '/reset-password/');

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-reset_password-hidden-csrf'] = $_SESSION['csrf'];
        }

        $userDBBefore = static::$db->selectRow('SELECT * FROM users WHERE id = 30');

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/reset-password/', $params, [], [], $queryParams);
        if ($hasRedirection) {
            $userDBAfter = static::$db->selectRow('SELECT * FROM users WHERE id = 30');
            if ($isFormSuccess) {
                static::assertNotSame($userDBBefore, $userDBAfter);
                static::assertNull($userDBAfter['password_reset']);
                static::assertNull($userDBAfter['password_reset_at']);
            } else {
                static::assertSame($userDBBefore, $userDBAfter);
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', '/reset-password/', [], [], [], $queryParams);
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['success']['message']);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        if ($isFormSuccess) {
            return;
        }

        // test fields HTML
        $fields = ['email', 'password', 'password_confirm'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'email') {
                $value = $hasValue ? Helper::trim($params['form-reset_password-input-email']) : '';
                $this->doTestHtmlForm($response, '/reset-password/?reset_token=' . Security::escAttr($resetToken), $this->getHTMLFieldEmail($value, $hasError, $labelError));
            }

            if ($field === 'password') {
                $this->doTestHtmlForm($response, '/reset-password/?reset_token=' . Security::escAttr($resetToken), $this->getHTMLFieldPassword($hasError, $labelError));
            }

            if ($field === 'password_confirm') {
                $this->doTestHtmlForm($response, '/reset-password/?reset_token=' . Security::escAttr($resetToken), $this->getHTMLFieldPasswordConfirm($hasError, $labelError));
            }
        }
    }

    /** @throws SecurityException */
    protected function getHTMLFieldEmail(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-reset_password-label-email form-reset_password-label-email-error" aria-required="true" autocomplete="email" class="form__input form__input--invisible form__input--error" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-reset_password-input-email" name="form-reset_password-input-email" type="text" value="$v"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-reset_password-input-email" id="form-reset_password-label-email-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-reset_password-label-email" aria-required="true" autocomplete="email" class="form__input form__input--invisible" data-form-error-email="Email is invalid" data-form-has-container data-form-rules="email" id="form-reset_password-input-email" name="form-reset_password-input-email" type="text" value="$v"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    protected function getHTMLFieldPassword(bool $hasError, string $labelError): string
    {
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-describedby="form-reset_password-span-password" aria-invalid="false" aria-labelledby="form-reset_password-label-password form-reset_password-label-password-error" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible form__input--error" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-reset_password-input-password" name="form-reset_password-input-password" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-reset_password-input-password" id="form-reset_password-label-password-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-describedby="form-reset_password-span-password" aria-invalid="false" aria-labelledby="form-reset_password-label-password" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-reset_password-input-password" name="form-reset_password-input-password" type="password"/>
<span class="form__feedback"></span>
</div>
HTML;
    }

    protected function getHTMLFieldPasswordConfirm(bool $hasError, string $labelError): string
    {
        if ($hasError) {
            return <<<HTML
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-reset_password-label-password_confirm form-reset_password-label-password_confirm-error" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible form__input--error" data-form-error-equal_field="Confirm New Password must be the same as New Password" data-form-error-required="Confirm New Password is required" data-form-has-container data-form-rules="required|equal_field:form-reset_password-input-password" id="form-reset_password-input-password_confirm" name="form-reset_password-input-password_confirm" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-reset_password-input-password_confirm" id="form-reset_password-label-password_confirm-error">$labelError</label>
HTML;
        }

        return <<<HTML
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-reset_password-label-password_confirm" aria-required="true" autocomplete="new-password" class="form__input form__input--invisible" data-form-error-equal_field="Confirm New Password must be the same as New Password" data-form-error-required="Confirm New Password is required" data-form-has-container data-form-rules="required|equal_field:form-reset_password-input-password" id="form-reset_password-input-password_confirm" name="form-reset_password-input-password_confirm" type="password"/>
<span class="form__feedback"></span>
</div>
HTML;
    }
}
