<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

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

/** @internal */
class ProfileEditPOSTDeleteProfileTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Crypt\CryptException
     * @throws DatabaseException
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

    public static function provideDeleteProfileDataCases(): iterable
    {
        yield 'delete OK - give blueprints - keep comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - delete blueprints - keep comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'delete',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - give blueprints - anonymize comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'anonymize',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - delete blueprints - anonymize comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'delete',
                'form-delete_profile-select-comments_ownership'   => 'anonymize',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - give blueprints - delete comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'delete',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - delete blueprints - delete comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'delete',
                'form-delete_profile-select-comments_ownership'   => 'delete',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete OK - no anonymous user - delete blueprints even if "give" sent - anonymize comments' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'anonymize',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => false
        ];

        yield 'csrf incorrect' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'incorrect_csrf',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => false,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'missing fields - no csrf' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => false,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'missing fields - no blueprints_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'               => 'csrf_is_replaced',
                'form-delete_profile-select-comments_ownership' => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error, missing fields</div>'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'missing fields - no comments_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error, missing fields</div>'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'empty fields - blueprints_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => ' ',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on blueprints ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['blueprints_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'blueprints_ownership' => 'Blueprints Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'empty fields - comments_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => ' ',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on comments ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['comments_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'comments_ownership' => 'Comments Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid fields - blueprints_ownership invalid (keep-comments)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'invalid',
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on blueprints ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['blueprints_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'blueprints_ownership' => 'Blueprints Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid fields - blueprints_ownership invalid (anonymize-comments)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'invalid',
                'form-delete_profile-select-comments_ownership'   => 'anonymize',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on blueprints ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['blueprints_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'blueprints_ownership' => 'Blueprints Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid fields - blueprints_ownership invalid (delete-comments)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'invalid',
                'form-delete_profile-select-comments_ownership'   => 'delete',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on blueprints ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['blueprints_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'blueprints_ownership' => 'Blueprints Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid fields - comments_ownership invalid (give-blueprints)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => 'invalid',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on comments ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['comments_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'comments_ownership' => 'Comments Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid fields - comments_ownership invalid (delete-blueprints)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'delete',
                'form-delete_profile-select-comments_ownership'   => 'invalid',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error(s) on comments ownership</div>'
                ]
            ],
            'fieldsHasError'   => ['comments_ownership'],
            'fieldsHasValue'   => ['blueprints_ownership', 'comments_ownership'],
            'fieldsLabelError' => [
                'comments_ownership' => 'Comments Ownership is invalid'
            ],
            'hasAnonymousUser' => true
        ];

        yield 'invalid encoding fields - blueprints_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => \chr(99999999),
                'form-delete_profile-select-comments_ownership'   => 'keep',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'invalid encoding fields - comments_ownership' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'give',
                'form-delete_profile-select-comments_ownership'   => \chr(99999999),
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];

        yield 'delete KO - delete user failed with exception (always to run last)' => [
            'sqlQueries' => [
                "REPLACE INTO users (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`) VALUES (189, 'user_189', null, 'user_189', 'user_189@example.com', 'member', UTC_TIMESTAMP(), null)",
                'REPLACE INTO users_infos (`id_user`) VALUES (189)',
                "REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC')",
                "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                "REPLACE INTO comments (`id`, `id_author`, `id_blueprint`, `content`, `created_at`) VALUES (50, 189, 80, 'my comment', utc_timestamp())",
            ],
            'userID' => 189,
            'params' => [
                'form-delete_profile-hidden-csrf'                 => 'csrf_is_replaced',
                'form-delete_profile-select-blueprints_ownership' => 'delete',
                'form-delete_profile-select-comments_ownership'   => 'delete',
                'raise_exception'                                 => true,
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_profile">'
                ],
                'error' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_profile" role="alert">Error, could not delete your profile</div>'
                ]
            ],
            'fieldsHasError'   => [],
            'fieldsHasValue'   => [],
            'fieldsLabelError' => [],
            'hasAnonymousUser' => true
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideDeleteProfileDataCases')]
    public function testProfileEditPOSTDeleteProfile(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError, bool $hasAnonymousUser): void
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
            $params['form-delete_profile-hidden-csrf'] = $_SESSION['csrf'];
        }

        // database before
        $userBefore = static::$db->selectRow('SELECT * FROM users WHERE id = ' . $userID);
        $userInfosBefore = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);
        $userApiBefore = static::$db->selectRow('SELECT * FROM users_api WHERE id_user = ' . $userID);
        $blueprintsBefore = static::$db->selectRow('SELECT * FROM blueprints WHERE id_author = ' . $userID);
        $commentsBefore = static::$db->selectRow('SELECT * FROM comments WHERE id_author = ' . $userID);

        if (isset($params['raise_exception'])) {
            static::$db->dropTables('users_api');
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params, [], [], [], [], [], [], $envFile);

        if (isset($params['raise_exception'])) {
            $sql = <<<'SQL'
                create table if not exists users_api
                (
                    id_user int unsigned not null
                        primary key,
                    api_key varchar(100) not null,
                    constraint api_key_UNIQUE
                        unique (api_key)
                )
                charset=utf8mb4;
                REPLACE INTO users_api (`id_user`, `api_key`) VALUES (189, 'ABC');
            SQL;

            static::$db->exec($sql);
        }

        if ($hasRedirection) {
            if ($isFormSuccess) {
                static::assertSame('/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'), [], [], [], [], [], [], [], $envFile);
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // user after
        $userAfter = static::$db->selectRow('SELECT * FROM users WHERE id = ' . $userID);
        $userInfosAfter = static::$db->selectRow('SELECT * FROM users_infos WHERE id_user = ' . $userID);
        $userApiAfter = static::$db->selectRow('SELECT * FROM users_api WHERE id_user = ' . $userID);
        $blueprintsAfter = static::$db->selectRow('SELECT * FROM blueprints WHERE id_author = ' . $userID);
        $commentsAfter = static::$db->selectRow('SELECT * FROM comments WHERE id_author = ' . $userID);

        if ($isFormSuccess) {
            static::assertNotSame($userBefore, $userAfter);
            static::assertNotSame($userInfosBefore, $userInfosAfter);
            static::assertNotSame($userApiBefore, $userApiAfter);
            static::assertNotSame($blueprintsBefore, $blueprintsAfter);
            static::assertNotSame($commentsBefore, $commentsAfter);

            if ($params['form-delete_profile-select-blueprints_ownership'] === 'give' && ((int) Application::getConfig()->get('ANONYMOUS_ID') !== 0)) {
                $blueprint = static::$db->selectRow('SELECT * FROM blueprints WHERE id = ' . $blueprintsBefore['id']);
                static::assertSame(static::$anonymousID, (int) $blueprint['id_author']);
            } elseif ($params['form-delete_profile-select-blueprints_ownership'] === 'delete') {
                $blueprint = static::$db->selectRow('SELECT * FROM blueprints WHERE id = ' . $blueprintsBefore['id']);
                static::assertNotNull($blueprint['deleted_at']);
                static::assertNull($blueprint['id_author']);
            }

            if ($params['form-delete_profile-select-comments_ownership'] === 'keep') {
                $comment = static::$db->selectRow('SELECT * FROM comments WHERE id = ' . $commentsBefore['id']);
                static::assertNull($comment['id_author']);
                static::assertSame($userBefore['username'], $comment['name_fallback']);
            } elseif ($params['form-delete_profile-select-comments_ownership'] === 'anonymize') {
                $comment = static::$db->selectRow('SELECT * FROM comments WHERE id = ' . $commentsBefore['id']);
                static::assertNull($comment['id_author']);
                static::assertSame('Guest', $comment['name_fallback']);
            } elseif ($params['form-delete_profile-select-comments_ownership'] === 'delete') {
                $comment = static::$db->count('SELECT COUNT(*) FROM comments WHERE id = ' . $commentsBefore['id']);
                static::assertSame(0, $comment);
            }
        } else {
            static::assertSame($userBefore, $userAfter);
            static::assertSame($userInfosBefore, $userInfosAfter);
            static::assertSame($userApiBefore, $userApiAfter);
            static::assertSame($blueprintsBefore, $blueprintsAfter);
            static::assertSame($commentsBefore, $commentsAfter);
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
        $fields = ['blueprints_ownership', 'comments_ownership'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'blueprints_ownership') {
                $value = $hasValue ? Helper::trim($params['form-delete_profile-select-blueprints_ownership']) : '';
                $this->doTestHtmlForm($response, '#form-delete_profile', $this->getHTMLFieldBlueprintsOwnership($value, $hasError, $labelError));
            }

            if ($field === 'comments_ownership') {
                $value = $hasValue ? Helper::trim($params['form-delete_profile-select-comments_ownership']) : '';
                $this->doTestHtmlForm($response, '#form-delete_profile', $this->getHTMLFieldCommentsOwnership($value, $hasError, $labelError));
            }
        }
    }

    protected function getHTMLFieldBlueprintsOwnership(string $value, bool $hasError, string $labelError): string
    {
        $give = ($value === 'give' || $value === '') ? ' selected="selected"' : '';
        $delete = ($value === 'delete') ? ' selected="selected"' : '';

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_profile-select-blueprints_ownership" id="form-delete_profile-label-blueprints_ownership">Blueprints ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_profile-label-blueprints_ownership form-delete_profile-label-blueprints_ownership-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-delete_profile-select-blueprints_ownership" name="form-delete_profile-select-blueprints_ownership">
<option value="give"{$give}>Give my blueprints to anonymous user</option>
<option value="delete"{$delete}>Delete my blueprints</option>
</select>
</div>
<label class="form__label form__label--error" for="form-delete_profile-select-blueprints_ownership" id="form-delete_profile-label-blueprints_ownership-error">{$labelError}</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_profile-select-blueprints_ownership" id="form-delete_profile-label-blueprints_ownership">Blueprints ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_profile-label-blueprints_ownership" aria-required="true" class="form__input form__input--select" id="form-delete_profile-select-blueprints_ownership" name="form-delete_profile-select-blueprints_ownership">
<option value="give"{$give}>Give my blueprints to anonymous user</option>
<option value="delete"{$delete}>Delete my blueprints</option>
</select>
</div>
</div>
HTML;
    }

    protected function getHTMLFieldCommentsOwnership(string $value, bool $hasError, string $labelError): string
    {
        $keep = ($value === 'keep' || $value === '') ? ' selected="selected"' : '';
        $anonymize = ($value === 'anonymize') ? ' selected="selected"' : '';
        $delete = ($value === 'delete') ? ' selected="selected"' : '';

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_profile-select-comments_ownership" id="form-delete_profile-label-comments_ownership">Comments ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_profile-label-comments_ownership form-delete_profile-label-comments_ownership-error" aria-required="true" class="form__input form__input--select form__input--error" id="form-delete_profile-select-comments_ownership" name="form-delete_profile-select-comments_ownership">
<option value="keep"{$keep}>Keep my name and comments</option>
<option value="anonymize"{$anonymize}>Use guest name and keep comments</option>
<option value="delete"{$delete}>Delete comments</option>
</select>
</div>
<label class="form__label form__label--error" for="form-delete_profile-select-comments_ownership" id="form-delete_profile-label-comments_ownership-error">{$labelError}</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-delete_profile-select-comments_ownership" id="form-delete_profile-label-comments_ownership">Comments ownership</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-delete_profile-label-comments_ownership" aria-required="true" class="form__input form__input--select" id="form-delete_profile-select-comments_ownership" name="form-delete_profile-select-comments_ownership">
<option value="keep"{$keep}>Keep my name and comments</option>
<option value="anonymize"{$anonymize}>Use guest name and keep comments</option>
<option value="delete"{$delete}>Delete comments</option>
</select>
</div>
</div>
HTML;
    }
}
