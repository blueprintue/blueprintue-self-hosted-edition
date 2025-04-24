<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintPOSTDeleteVersionBlueprintTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::addUsers();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing list blueprint's versions actions.
     *
     * @return array[]
     */
    public static function dataCasesBlueprintPOST_DeleteVersionBlueprint(): array
    {
        return [
            'visitor - no button delete version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => null,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => false,
                'params'                 => null,
                'useCsrfFromSession'     => false,
                'hasRedirection'         => false,
                'isFormSuccess'          => false,
                'flashMessages'          => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'user - no button delete version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 55,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => false,
                'params'                 => null,
                'useCsrfFromSession'     => false,
                'hasRedirection'         => false,
                'isFormSuccess'          => false,
                'flashMessages'          => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'author - has button delete version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => false,
                'params'                 => null,
                'useCsrfFromSession'     => false,
                'hasRedirection'         => false,
                'isFormSuccess'          => false,
                'flashMessages'          => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'user who post as anonymous - no button delete version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 55,
                'anonymousBlueprints'    => [1, 2, 3],
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => false,
                'params'                 => null,
                'useCsrfFromSession'     => false,
                'hasRedirection'         => false,
                'isFormSuccess'          => false,
                'flashMessages'          => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid delete version 1' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">Version 1 has been deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid delete version 2' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 2, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '2',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">Version 2 has been deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'csrf incorrect' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'incorrect_csrf',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'missing fields - no version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">Error, missing fields</div>'
                    ]
                ],
            ],
            'do invalid delete version - user has no right' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 55,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">Error, delete version is invalid on this blueprint</div>'
                    ]
                ],
            ],
            'do invalid delete version - version incorrect (4)' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '4',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">Error, version to delete is invalid</div>'
                    ]
                ],
            ],
            'do invalid delete version - version incorrect (invalid)' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => true,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => 'invalid',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">Error, version to delete is invalid</div>'
                    ]
                ],
            ],
            'do invalid delete version - has to have one version left' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => 1,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">Error, blueprint must have one version left</div>'
                    ]
                ],
            ],
            'do invalid delete version - visitor has no right' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => null,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
            'invalid encoding fields - version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                   => 'slug_public',
                'userID'                 => null,
                'anonymousBlueprints'    => null,
                'hasButtonDeleteVersion' => false,
                'doPostAction'           => true,
                'params'                 => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_version_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_version_blueprint" role="alert">'
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_DeleteVersionBlueprint
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('dataCasesBlueprintPOST_DeleteVersionBlueprint')]
    public function testBlueprintPOSTDeleteVersionBlueprint(array $sqlQueries, string $slug, ?int $userID, ?array $anonymousBlueprints, bool $hasButtonDeleteVersion, bool $doPostAction, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages): void
    {
        static::cleanFiles();

        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user and anonymous blueprints in $_SESSION
        $session = ['remove' => [], 'set' => []];
        if ($userID !== null) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        if ($anonymousBlueprints !== null) {
            $session['set']['anonymous_blueprints'] = $anonymousBlueprints;
        } else {
            $session['remove'][] = 'anonymous_blueprints';
        }

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // put csrf
        if ($doPostAction && $useCsrfFromSession) {
            $params['form-delete_version_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        if ($hasButtonDeleteVersion) {
            $this->doTestHtmlMain($response, '<input name="form-delete_version_blueprint-hidden-version" type="hidden" value="1"/>');
        } else {
            $this->doTestHtmlMainNot($response, '<input name="form-delete_version_blueprint-hidden-version" type="hidden" value="1"/>');
        }

        // stop test if no post action needed
        if ($doPostAction === false) {
            return;
        }

        // set files
        $this->createBlueprintFile('a');
        $this->createBlueprintFile('a', '2');

        // do post action
        $response = $this->getResponseFromApplication('POST', '/blueprint/' . $slug . '/', $params);

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            static::assertSame('/blueprint/' . $slug . '/', $response->getHeaderLine('Location'));
            $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['success']['message']);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlMain($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlMainNot($response, $flashMessages['error']['message']);
        }

        if ($isFormSuccess) {
            $blueprintVersions = static::$db->selectAll('SELECT * FROM blueprints_version WHERE id_blueprint = 1');
            static::assertCount(1, $blueprintVersions);
            if ($params['form-delete_version_blueprint-hidden-version'] === '1') {
                static::assertSame('2', (string) $blueprintVersions[0]['version']);
            } else {
                static::assertSame('1', (string) $blueprintVersions[0]['version']);
            }

            // file check
            $caracters = \mb_str_split('a');
            $subfolder = '';
            foreach ($caracters as $c) {
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }
            $subfolder = \mb_strtolower($subfolder);

            $storageFolder = \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'storage_test' . \DIRECTORY_SEPARATOR;

            if ($params['form-delete_version_blueprint-hidden-version'] === '1') {
                static::assertFileExists($storageFolder . $subfolder . 'a-2.txt');
                static::assertFileDoesNotExist($storageFolder . $subfolder . 'a-1.txt');
            } else {
                static::assertFileDoesNotExist($storageFolder . $subfolder . 'a-2.txt');
                static::assertFileExists($storageFolder . $subfolder . 'a-1.txt');
            }
        }
    }
}
