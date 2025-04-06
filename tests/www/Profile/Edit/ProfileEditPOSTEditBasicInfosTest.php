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

class ProfileEditPOSTEditBasicInfosTest extends TestCase
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

    public static function dataCasesEditBasicInfos(): array
    {
        return [
            'edit OK' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (`id_user`, `bio`, `link_website`) VALUES (189, 'bio_value\nline 2', 'link_website_value')"
                ],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">Your basic informations has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => ['bio', 'website'],
            ],
            'edit OK - xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (`id_user`, `bio`, `link_website`) VALUES (189, 'bio_value\nline 2', 'link_website_value')"
                ],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => '<script>alert("bio");</script>',
                    'form-edit_basic_infos-input-website' => '<script>alert("website");</script>',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">Your basic informations has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => ['bio', 'website'],
            ],
            'edit OK - missing users_infos' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">Your basic informations has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => ['bio', 'website'],
            ],
            'csrf incorrect' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'incorrect_csrf',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'missing fields - no fields' => [
                'sqlQueries'            => [],
                'userID'                => 189,
                'params'                => [],
                'useCsrfFromSession'    => false,
                'hasRedirection'        => false,
                'isFormSuccess'         => false,
                'flashMessages'         => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'missing fields - no bio' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'missing fields - no website' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'edit OK - empty fields - bio empty' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (`id_user`, `bio`, `link_website`) VALUES (189, 'bio_value\nline 2', 'link_website_value')"
                ],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => ' ',
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">Your basic informations has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => ['bio', 'website'],
            ],
            'edit OK - empty fields - website empty' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (`id_user`, `bio`, `link_website`) VALUES (189, 'bio_value\nline 2', 'link_website_value')"
                ],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">Your basic informations has been saved</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => ['bio', 'website'],
            ],
            'invalid encoding fields - bio' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => \chr(99999999),
                    'form-edit_basic_infos-input-website' => 'my-website',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
            'invalid encoding fields - website' => [
                'sqlQueries'  => [],
                'userID'      => 189,
                'params'      => [
                    'form-edit_basic_infos-hidden-csrf'   => 'csrf_is_replaced',
                    'form-edit_basic_infos-textarea-bio'  => "my\nbio",
                    'form-edit_basic_infos-input-website' => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_basic_infos">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_basic_infos" role="alert">'
                    ]
                ],
                'fieldsHasError' => [],
                'fieldsHasValue' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesEditBasicInfos
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesEditBasicInfos')]
    public function testProfileEditPOSTEditBasicInfos(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue): void
    {
        static::setDatabase();
        static::$db->truncateTables('users_infos');

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
            $params['form-edit_basic_infos-hidden-csrf'] = $_SESSION['csrf'];
        }

        // infos before
        $usersInfosBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);

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

        $usersInfosAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);

        if ($isFormSuccess) {
            static::assertNotSame($usersInfosBefore, $usersInfosAfter);
            static::assertSame(Helper::trim($params['form-edit_basic_infos-textarea-bio']), $usersInfosAfter['bio']);
            static::assertSame(Helper::trim($params['form-edit_basic_infos-input-website']), $usersInfosAfter['link_website']);
        } else {
            static::assertSame($usersInfosBefore, $usersInfosAfter);
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

        // test fields HTML
        $fields = ['bio', 'website'];
        foreach ($fields as $field) {
            $hasValue = \in_array($field, $fieldsHasValue, true);

            if ($field === 'bio') {
                $value = $hasValue ? Helper::trim($params['form-edit_basic_infos-textarea-bio']) : '';
                $this->doTestHtmlForm($response, '#form-edit_basic_infos', $this->getHTMLFieldBio($value));
            }

            if ($field === 'website') {
                $value = $hasValue ? Helper::trim($params['form-edit_basic_infos-input-website']) : '';
                $this->doTestHtmlForm($response, '#form-edit_basic_infos', $this->getHTMLFieldWebsite($value));
            }
        }
    }

    /**
     * @throws SecurityException
     */
    protected function getHTMLFieldBio(string $value): string
    {
        $v = Security::escHTML($value);

        // phpcs:disable
        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_basic_infos-textarea-bio" id="form-edit_basic_infos-label-bio">Bio</label>
<textarea aria-invalid="false" aria-labelledby="form-edit_basic_infos-label-bio" class="form__input form__input--textarea" id="form-edit_basic_infos-textarea-bio" name="form-edit_basic_infos-textarea-bio">$v</textarea>
</div>
HTML;
        // phpcs:enable
    }

    /**
     * @throws SecurityException
     */
    protected function getHTMLFieldWebsite(string $value): string
    {
        $v = Security::escAttr($value);

        // phpcs:disable
        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-edit_basic_infos-input-website" id="form-edit_basic_infos-label-website">Website</label>
<input aria-invalid="false" aria-labelledby="form-edit_basic_infos-label-website" class="form__input" id="form-edit_basic_infos-input-website" name="form-edit_basic_infos-input-website" type="text" value="$v"/>
</div>
HTML;
        // phpcs:enable
    }
}
