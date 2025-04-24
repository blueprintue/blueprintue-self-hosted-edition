<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Blueprint\Edit;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintEditPOSTDeleteBlueprintTest extends TestCase
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

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function dataCasesDeleteBlueprint(): array
    {
        return [
            'delete OK - give blueprint - public blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'give',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'delete OK - give blueprint - unlisted blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'give',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'delete KO - give blueprint - private blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'give',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error(s) on ownership</div>'
                    ]
                ],
                'fieldsHasError'   => ['ownership'],
                'fieldsHasValue'   => ['ownership'],
                'fieldsLabelError' => [
                    'ownership' => 'Ownership is invalid, you can&#039;t give blueprint when having private exposure'
                ],
                'hasAnonymousUser' => true
            ],
            'delete OK - delete blueprint - public blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'delete',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'delete OK - delete blueprint - unlisted blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'delete',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'delete OK - delete blueprint - private blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'delete',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'delete OK - no anonymous user - delete blueprint even if "give" sent - public blueprint' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`, `count_private_blueprint`, `count_public_blueprint`) VALUES (189, 1, 1), (' . static::$anonymousID . ', 1, 1)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'give',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => false
            ],
            'csrf incorrect' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'incorrect_csrf',
                    'form-delete_blueprint-select-ownership' => 'delete',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-select-ownership' => 'delete',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'missing fields - no ownership' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
            'empty fields - ownership' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error(s) on ownership</div>'
                    ]
                ],
                'fieldsHasError'   => ['ownership'],
                'fieldsHasValue'   => ['ownership'],
                'fieldsLabelError' => [
                    'ownership' => 'Ownership is invalid'
                ],
                'hasAnonymousUser' => true
            ],
            'invalid fields - ownership invalid' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error(s) on ownership</div>'
                    ]
                ],
                'fieldsHasError'   => ['ownership'],
                'fieldsHasValue'   => ['ownership'],
                'fieldsLabelError' => [
                    'ownership' => 'Ownership is invalid'
                ],
                'hasAnonymousUser' => true
            ],
            'invalid fields - ownership give is not possible with private exposure' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => 'give',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error(s) on ownership</div>'
                    ]
                ],
                'fieldsHasError'   => ['ownership'],
                'fieldsHasValue'   => ['ownership'],
                'fieldsLabelError' => [
                    'ownership' => 'Ownership is invalid, you can&#039;t give blueprint when having private exposure'
                ],
                'hasAnonymousUser' => true
            ],
            'invalid encoding fields - select-ownership' => [
                'sqlQueries' => [
                    'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_blueprint-hidden-csrf'      => 'csrf_is_replaced',
                    'form-delete_blueprint-select-ownership' => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error(s) on ownership</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
                'hasAnonymousUser' => true
            ],
        ];
    }

    /**
     * @dataProvider dataCasesDeleteBlueprint
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('dataCasesDeleteBlueprint')]
    public function testBlueprintEditPOSTDeleteBlueprint(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError, bool $hasAnonymousUser): void
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

        $envFile = 'tests.env';
        if (!$hasAnonymousUser) {
            $envFile = 'tests-no-anonymous-user.env';
        }

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues, [], [], [], [], [], $envFile);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-delete_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        }

        // database before
        $blueprintBefore = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');
        $userInfosBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);
        $userInfosAnonymousBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . static::$anonymousID);

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/blueprint/slug_1/edit/', $params, [], [], [], [], [], [], $envFile);

        if ($hasRedirection) {
            if ($isFormSuccess) {
                static::assertSame('/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/blueprint/slug_1/edit/', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'), [], [], [], [], [], [], [], $envFile);
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // database after
        $blueprintAfter = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');
        $userInfosAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);
        $userInfosAnonymousAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . static::$anonymousID);

        if ($isFormSuccess) {
            static::assertNotSame($blueprintBefore, $blueprintAfter);
            static::assertNotSame($userInfosBefore, $userInfosAfter);

            if ($params['form-delete_blueprint-select-ownership'] === 'give' && ((int) Application::getConfig()->get('ANONYMOUS_ID') !== 0)) {
                // blueprint
                static::assertSame(static::$anonymousID, (int) $blueprintAfter['id_author']);

                // counters
                static::assertSame(((int) $userInfosBefore['count_private_blueprint']) - 1, (int) $userInfosAfter['count_private_blueprint']);
                if ($blueprintBefore['exposure'] !== 'private') {
                    static::assertNotSame($userInfosAnonymousBefore, $userInfosAnonymousAfter);
                    static::assertSame(((int) $userInfosBefore['count_public_blueprint']) - 1, (int) $userInfosAfter['count_public_blueprint']);
                } else {
                    static::assertSame($userInfosAnonymousBefore, $userInfosAnonymousAfter);
                    static::assertSame((int) $userInfosBefore['count_public_blueprint'], (int) $userInfosAfter['count_public_blueprint']);
                }
            } elseif ($params['form-delete_blueprint-select-ownership'] === 'delete') {
                // blueprint
                static::assertNotNull($blueprintAfter['deleted_at']);
                static::assertSame(0, (int) $blueprintAfter['id_author']);

                // counters
                static::assertSame($userInfosAnonymousBefore, $userInfosAnonymousAfter);
                static::assertSame(((int) $userInfosBefore['count_private_blueprint']) - 1, (int) $userInfosAfter['count_private_blueprint']);
                if ($blueprintBefore['exposure'] !== 'private') {
                    static::assertSame(((int) $userInfosBefore['count_public_blueprint']) - 1, (int) $userInfosAfter['count_public_blueprint']);
                } else {
                    static::assertSame((int) $userInfosBefore['count_public_blueprint'], (int) $userInfosAfter['count_public_blueprint']);
                }
            }
        } else {
            static::assertSame($blueprintBefore, $blueprintAfter);
            static::assertSame($userInfosBefore, $userInfosAfter);
            static::assertSame($userInfosAnonymousBefore, $userInfosAnonymousAfter);
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

        if ($isFormSuccess) {
            return;
        }

        // test fields HTML
        $fields = ['ownership'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'ownership') {
                $value = $hasValue ? Helper::trim($params['form-delete_blueprint-select-ownership']) : '';
                $this->doTestHtmlForm($response, '#form-delete_blueprint', $this->getHTMLFieldOwnership($value, $hasError, $labelError, $blueprintBefore['exposure'] === 'private'));
            }
        }
    }

    protected function getHTMLFieldOwnership(string $value, bool $hasError, string $labelError, bool $isDisabled): string
    {
        $give = ($value === 'give') ? ' selected="selected"' : '';
        $delete = ($value === 'delete') ? ' selected="selected"' : '';

        if ($isDisabled) {
            $give = ' disabled="disabled"';
        }

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_blueprint-select-ownership" id="form-delete_blueprint-label-ownership">Blueprints ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_blueprint-label-ownership form-delete_blueprint-label-ownership-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-delete_blueprint-select-ownership" name="form-delete_blueprint-select-ownership">
<option value="give"{$give}>Give my blueprint to anonymous user</option>
<option value="delete"{$delete}>Delete my blueprint</option>
</select>
</div>
<label class="form__label form__label--error" for="form-delete_blueprint-select-ownership" id="form-delete_blueprint-label-ownership-error">{$labelError}</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_blueprint-select-ownership" id="form-delete_blueprint-label-ownership">Blueprints ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_blueprint-label-ownership" aria-required="true" class="form__input form__input--select" id="form-delete_blueprint-select-ownership" name="form-delete_blueprint-select-ownership">
<option value="give"{$give}>Give my blueprint to anonymous user</option>
<option value="delete"{$delete}>Delete my blueprint</option>
</select>
</div>
</div>
HTML;
    }
}
