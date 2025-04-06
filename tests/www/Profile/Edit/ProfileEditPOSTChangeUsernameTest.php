<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class ProfileEditPOSTChangeUsernameTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws \Rancoud\Crypt\CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 189,
            'username' => 'user_189',
            'hash'     => Crypt::hash('password_user_189'),
            'slug'     => 'user_189',
            'email'    => 'user_189@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 195,
            'username' => 'user_195',
            'hash'     => Crypt::hash('password_user_195'),
            'slug'     => 'user_195',
            'email'    => null,
            'grade'    => 'member',
            'avatar'   => 'formage.jpg',
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 199,
            'username' => 'user_199 <script>alert(1)</script>',
            'hash'     => Crypt::hash('password_user_199'),
            'slug'     => 'user_199',
            'email'    => 'user_199@example.com',
            'grade'    => 'member',
            'avatar'   => 'mem\"><script>alert(1)</script>fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function dataCasesChangeUsername(): array
    {
        return [
            'edit OK' => [
                'sqlQueries' => [
                    "UPDATE users SET slug = 'user_189', username = 'user_189' WHERE id = 189"
                ],
                'userID' => 189,
                'params' => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => 'user_user',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">Your new username has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'edit KO - xss' => [
                'sqlQueries' => [
                    "UPDATE users SET slug = 'user_189', username = 'user_189' WHERE id = 189"
                ],
                'userID' => 189,
                'params' => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => 'user_user<script>alert("facebook");</script>',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">Error(s) on new username</div>'
                    ]
                ],
                'fieldsHasError'   => ['new_username'],
                'fieldsHasValue'   => ['new_username'],
                'fieldsLabelError' => [
                    'new_username' => 'Username is invalid',
                ],
            ],
            'csrf incorrect' => [
                'sqlQueries' => [],
                'userID'     => 189,
                'params'     => [
                    'form-change_username-hidden-csrf'        => 'incorrect_csrf',
                    'form-change_username-input-new_username' => 'user_user'
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'sqlQueries'         => [],
                'userID'             => 189,
                'params'             => [],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [],
                'userID'     => 189,
                'params'     => [
                    'form-change_username-input-new_username' => 'user_user',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no username' => [
                'sqlQueries' => [],
                'userID'     => 189,
                'params'     => [
                    'form-change_username-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - username empty' => [
                'sqlQueries' => [
                    "UPDATE users SET slug = 'user_189', username = 'user_189' WHERE id = 189"
                ],
                'userID' => 189,
                'params' => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">Error(s) on new username</div>'
                    ]
                ],
                'fieldsHasError'   => ['new_username'],
                'fieldsHasValue'   => ['new_username'],
                'fieldsLabelError' => [
                    'new_username' => 'Username is required',
                ],
            ],
            'invalid fields - invalid username' => [
                'sqlQueries' => [
                    "UPDATE users SET slug = 'user_189', username = 'user_189' WHERE id = 189"
                ],
                'userID' => 189,
                'params' => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => 'user<script>alert("facebook");</script>',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">Error(s) on new username</div>'
                    ]
                ],
                'fieldsHasError'   => ['new_username'],
                'fieldsHasValue'   => ['new_username'],
                'fieldsLabelError' => [
                    'new_username' => 'Username is invalid',
                ],
            ],
            'invalid fields - unavailable username' => [
                'sqlQueries' => [
                    "UPDATE users SET slug = 'user_189', username = 'user_189' WHERE id = 189"
                ],
                'userID' => 189,
                'params' => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => 'user_189',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">Error(s) on new username</div>'
                    ]
                ],
                'fieldsHasError'   => ['new_username'],
                'fieldsHasValue'   => ['new_username'],
                'fieldsLabelError' => [
                    'new_username' => 'Username is unavailable',
                ],
            ],
            'invalid encoding fields - new_username' => [
                'sqlQueries' => [],
                'userID'     => 189,
                'params'     => [
                    'form-change_username-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_username-input-new_username' => \chr(99999999)
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_username">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_username" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ]
        ];
    }

    /**
     * @dataProvider dataCasesChangeUsername
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesChangeUsername')]
    public function testProfileEditPOSTChangeUsername(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        static::setDatabase();

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => ['userID' => $userID, 'username' => 'user_189', 'slug' => 'user_189'],
            'remove' => []
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-change_username-hidden-csrf'] = $_SESSION['csrf'];
        }

        // user before
        $userBefore = static::$db->selectRow('SELECT * FROM users WHERE id = ' . $userID);

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params);

        if ($hasRedirection) {
            if ($isFormSuccess) {
                static::assertSame($params['form-change_username-input-new_username'], $_SESSION['username']);
                static::assertSame($params['form-change_username-input-new_username'], $_SESSION['slug']);
                static::assertSame('/profile/' . $params['form-change_username-input-new_username'] . '/edit/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('user_189', $_SESSION['username']);
                static::assertSame('user_189', $_SESSION['slug']);
                static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // user after
        $userAfter = static::$db->selectRow('SELECT * FROM users WHERE id = ' . $userID);

        if ($isFormSuccess) {
            static::assertNotSame($userBefore, $userAfter);
            static::assertSame(Helper::trim($params['form-change_username-input-new_username']), $userAfter['username']);
        } else {
            static::assertSame($userBefore, $userAfter);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['success']['message']);
        }

        $currentUsername = Security::escAttr($userAfter['username']);
        $this->doTestHtmlForm($response, '#form-change_username', <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_username-input-current_username" id="form-change_username-label-current_username">Current Username</label>
<input aria-labelledby="form-change_username-label-current_username" class="form__input form__input--disabled" disabled id="form-change_username-input-current_username" name="form-change_username-input-current_username" type="text" value="$currentUsername"/>
</div>
HTML);

        // test fields HTML
        $fields = ['new_username'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'new_username') {
                $value = $hasValue ? Helper::trim($params['form-change_username-input-new_username']) : '';
                $this->doTestHtmlForm($response, '#form-change_username', $this->getHTMLFieldNewUsername($value, $hasError, $labelError));
            }
        }
    }

    /**
     * @throws SecurityException
     */
    protected function getHTMLFieldNewUsername(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escAttr($value);

        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_username-input-new_username" id="form-change_username-label-new_username">New Username</label>
<div class="form__container form__container--error">
<input aria-invalid="false" aria-labelledby="form-change_username-label-new_username form-change_username-label-new_username-error" aria-required="true" class="form__input form__input--invisible form__input--error" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-change_username-input-new_username" name="form-change_username-input-new_username" type="text" value="$v"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-change_username-input-new_username" id="form-change_username-label-new_username-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_username-input-new_username" id="form-change_username-label-new_username">New Username</label>
<div class="form__container">
<input aria-invalid="false" aria-labelledby="form-change_username-label-new_username" aria-required="true" class="form__input form__input--invisible" data-form-error-regex="Expected username containing: digits, letters, symbols: - _ ." data-form-error-required="Username is required" data-form-has-container data-form-rules="required|regex:^[a-zA-Z0-9._ -]*$" id="form-change_username-input-new_username" name="form-change_username-input-new_username" type="text" value="$v"/>
<span class="form__feedback"></span>
</div>
</div>
HTML;
        // phpcs:enable
    }
}
