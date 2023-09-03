<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

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
    public function dataCasesBlueprintPOST_DeleteVersionBlueprint(): array
    {
        return [
            'visitor - no button delete version' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => null,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => false,
                'params'                    => null,
                'use_csrf_from_session'     => false,
                'has_redirection'           => false,
                'is_form_success'           => false,
                'flash_messages'            => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 55,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => false,
                'params'                    => null,
                'use_csrf_from_session'     => false,
                'has_redirection'           => false,
                'is_form_success'           => false,
                'flash_messages'            => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => false,
                'params'                    => null,
                'use_csrf_from_session'     => false,
                'has_redirection'           => false,
                'is_form_success'           => false,
                'flash_messages'            => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 55,
                'anonymous_blueprints'      => [1, 2, 3],
                'has_button_delete_version' => false,
                'do_post_action'            => false,
                'params'                    => null,
                'use_csrf_from_session'     => false,
                'has_redirection'           => false,
                'is_form_success'           => false,
                'flash_messages'            => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 2, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '2',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'incorrect_csrf',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 55,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '4',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => true,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => 'invalid',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => 1,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => null,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => '1',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
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
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                      => 'slug_public',
                'user_id'                   => null,
                'anonymous_blueprints'      => null,
                'has_button_delete_version' => false,
                'do_post_action'            => true,
                'params'                    => [
                    'form-delete_version_blueprint-hidden-csrf'    => 'csrf_is_replaced',
                    'form-delete_version_blueprint-hidden-version' => \chr(99999999),
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
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
     * @param array      $sqlQueries
     * @param string     $slug
     * @param int|null   $userID
     * @param array|null $anonymousBlueprints
     * @param bool       $hasButtonDeleteVersion
     * @param bool       $doPostAction
     * @param array|null $params
     * @param bool       $useCsrfFromSession
     * @param bool       $hasRedirection
     * @param bool       $isFormSuccess
     * @param array      $flashMessages
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     */
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
                static::assertSame('2', $blueprintVersions[0]['version']);
            } else {
                static::assertSame('1', $blueprintVersions[0]['version']);
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
