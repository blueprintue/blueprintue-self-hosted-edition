<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class ProfileEditPOSTChangePasswordTest extends TestCase
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

    public function dataCasesChangePassword(): array
    {
        return [
            'edit OK' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">Your new password has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'edit OK - xss' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*<script>alert("facebook");</script>',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*<script>alert("facebook");</script>',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">Your new password has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'csrf incorrect' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'incorrect_csrf',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no fields (password is null)' => [
                'sql_queries' => [
                    'UPDATE users SET password = NULL WHERE id = 189',
                ],
                'user_id'               => 189,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no csrf' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no new_password' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'missing fields - no new_password_confirm' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'        => 'csrf_is_replaced',
                    'form-change_password-input-new_password' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'empty fields - new_password empty' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => ' ',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must be at least 10 characters in length',
                ],
            ],
            'empty fields - new_password_confirm empty' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => ' ',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on confirm new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password_confirm'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password_confirm' => 'Password must be at least 10 characters in length',
                ],
            ],
            'invalid fields - new_password incorrect length' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'aze',
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must be at least 10 characters in length',
                ],
            ],
            'invalid fields - new_password incorrect format (miss lowercase)' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => '_*_123RTYY',
                    'form-change_password-input-new_password_confirm' => '_*_123RTYY',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters',
                ],
            ],
            'invalid fields - new_password incorrect format (miss uppercase)' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'aaze123_*_',
                    'form-change_password-input-new_password_confirm' => 'aaze123_*_',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters',
                ],
            ],
            'invalid fields - new_password incorrect format (miss digit)' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'aaze_*_RTY',
                    'form-change_password-input-new_password_confirm' => 'aaze_*_RTY',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters',
                ],
            ],
            'invalid fields - new_password incorrect format (miss special characters)' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'aaze123RTY',
                    'form-change_password-input-new_password_confirm' => 'aaze123RTY',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password' => 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters',
                ],
            ],
            'invalid fields - new_password_confirm incorrect length' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => 'aze',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on confirm new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password_confirm'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password_confirm' => 'Password must be at least 10 characters in length',
                ],
            ],
            'invalid fields - new_password and new_password_confirm incorrect length' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'aze',
                    'form-change_password-input-new_password_confirm' => 'aze',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on new password, confirm new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password', 'new_password_confirm'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password'         => 'Password must be at least 10 characters in length',
                    'new_password_confirm' => 'Password must be at least 10 characters in length',
                ],
            ],
            'invalid fields - new_password and new_password_confirm are different' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'my_secret_pasword_01*',
                    'form-change_password-input-new_password_confirm' => 'my_secret_pasword_02*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">Error(s) on confirm new password</div>'
                    ]
                ],
                'fields_has_error'      => ['new_password_confirm'],
                'fields_has_value'      => [],
                'fields_label_error'    => [
                    'new_password_confirm' => 'Confirm Password must be the same as Password',
                ],
            ],
            'invalid encoding fields - new_password' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => \chr(99999999),
                    'form-change_password-input-new_password_confirm' => 'My_secret_password_01*',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
            'invalid encoding fields - new_password_confirm' => [
                'sql_queries' => [],
                'user_id'     => 189,
                'params'      => [
                    'form-change_password-hidden-csrf'                => 'csrf_is_replaced',
                    'form-change_password-input-new_password'         => 'My_secret_password_01*',
                    'form-change_password-input-new_password_confirm' => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-change_password">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-change_password" role="alert">'
                    ]
                ],
                'fields_has_error'      => [],
                'fields_has_value'      => [],
                'fields_label_error'    => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesChangePassword
     *
     * @param array $sqlQueries
     * @param int   $userID
     * @param array $params
     * @param bool  $useCsrfFromSession
     * @param bool  $hasRedirection
     * @param bool  $isFormSuccess
     * @param array $flashMessages
     * @param array $fieldsHasError
     * @param array $fieldsHasValue
     * @param array $fieldsLabelError
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testProfileEditPOSTChangePassword(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        static::setDatabase();

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => ['userID' => $userID],
            'remove' => []
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-change_password-hidden-csrf'] = $_SESSION['csrf'];
        }

        // user before
        $userBefore = static::$db->selectRow('SELECT * FROM users WHERE id = ' . $userID);

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
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
            static::assertTrue(Crypt::verify($params['form-change_password-input-new_password'], $userAfter['password']));
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

        if ($userAfter['password'] === null) {
            $this->doTestHtmlForm($response, '#form-change_password', '<h2 class="block__title block__title--form">Add <span class="block__title--emphasis">password</span></h2>');
        } else {
            $this->doTestHtmlForm($response, '#form-change_password', '<h2 class="block__title block__title--form">Change <span class="block__title--emphasis">password</span></h2>');
        }

        // test fields HTML
        $fields = ['new_password', 'new_password_confirm'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'new_password') {
                $this->doTestHtmlForm($response, '#form-change_password', $this->getHTMLFieldNewPassword($hasError, $labelError));
            }

            if ($field === 'new_password_confirm') {
                $this->doTestHtmlForm($response, '#form-change_password', $this->getHTMLFieldNewPasswordConfirm($hasError, $labelError));
            }
        }
    }

    /**
     * @param bool   $hasError
     * @param string $labelError
     *
     * @return string
     */
    protected function getHTMLFieldNewPassword(bool $hasError, string $labelError): string
    {
        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_password-input-new_password" id="form-change_password-label-new_password">New Password</label>
<div class="form__container form__container--error">
<input aria-describedby="form-change_password-span-new_password" aria-invalid="false" aria-labelledby="form-change_password-label-new_password form-change_password-label-new_password-error" aria-required="true" class="form__input form__input--invisible form__input--error" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-change_password-input-new_password" name="form-change_password-input-new_password" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-change_password-input-new_password" id="form-change_password-label-new_password-error">$labelError</label>
<span class="form__help" id="form-change_password-span-new_password">Minimum of 10 characters with 1 digit and 1 uppercase and 1 lowercase and 1 special characters</span>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_password-input-new_password" id="form-change_password-label-new_password">New Password</label>
<div class="form__container">
<input aria-describedby="form-change_password-span-new_password" aria-invalid="false" aria-labelledby="form-change_password-label-new_password" aria-required="true" class="form__input form__input--invisible" data-form-error-min="Password must be at least 10 characters in length" data-form-error-regex="Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters" data-form-has-container data-form-rules="min:10|regex:^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$" id="form-change_password-input-new_password" name="form-change_password-input-new_password" type="password"/>
<span class="form__feedback"></span>
</div>
<span class="form__help" id="form-change_password-span-new_password">Minimum of 10 characters with 1 digit and 1 uppercase and 1 lowercase and 1 special characters</span>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @param bool   $hasError
     * @param string $labelError
     *
     * @return string
     */
    protected function getHTMLFieldNewPasswordConfirm(bool $hasError, string $labelError): string
    {
        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_password-input-new_password_confirm" id="form-change_password-label-new_password_confirm">Confirm New Password</label>
<div class="form__container form__container--error">
<input aria-describedby="form-change_password-span-new_password_confirm" aria-invalid="false" aria-labelledby="form-change_password-label-new_password_confirm form-change_password-label-new_password_confirm-error" aria-required="true" class="form__input form__input--invisible form__input--error" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-change_password-input-new_password" id="form-change_password-input-new_password_confirm" name="form-change_password-input-new_password_confirm" type="password"/>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-change_password-input-new_password_confirm" id="form-change_password-label-new_password_confirm-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-change_password-input-new_password_confirm" id="form-change_password-label-new_password_confirm">Confirm New Password</label>
<div class="form__container">
<input aria-describedby="form-change_password-span-new_password_confirm" aria-invalid="false" aria-labelledby="form-change_password-label-new_password_confirm" aria-required="true" class="form__input form__input--invisible" data-form-error-equal_field="Confirm Password must be the same as Password" data-form-error-required="Confirm Password is required" data-form-has-container data-form-rules="required|equal_field:form-change_password-input-new_password" id="form-change_password-input-new_password_confirm" name="form-change_password-input-new_password_confirm" type="password"/>
<span class="form__feedback"></span>
</div>
</div>
HTML;
        // phpcs:enable
    }
}
