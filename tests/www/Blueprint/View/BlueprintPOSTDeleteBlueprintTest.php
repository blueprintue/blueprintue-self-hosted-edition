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

class BlueprintPOSTDeleteBlueprintTest extends TestCase
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
     * Use for testing delete blueprint process.
     *
     * @return array[]
     */
    public function dataCasesBlueprintPOST_DeleteBlueprint(): array
    {
        return [
            'visitor - no button delete' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => null,
                'anonymous_blueprints'  => null,
                'has_button_delete'     => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'user - no button delete' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 55,
                'anonymous_blueprints'  => null,
                'has_button_delete'     => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'author - no button delete' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 1,
                'anonymous_blueprints'  => null,
                'has_button_delete'     => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'user who post as anonymous - has button delete' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 55,
                'anonymous_blueprints'  => [1, 2, 3],
                'has_button_delete'     => true,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid delete action on public blueprint - user' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    'TRUNCATE TABLE comments',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO comments (id_author, id_blueprint, content, created_at) VALUES (2, 579, 'aze', utc_timestamp()), (2, 1, 'aze', utc_timestamp()), (2, 1, 'aze', utc_timestamp()), (1, 1, 'aze', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (2, 1, 2, 10, 60)',
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (1, 0, 0, 8, 5)',
                ],
                'slug'                 => 'slug_public',
                'user_id'              => 55,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_delete'    => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid delete action on public blueprint - visitor' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    'TRUNCATE TABLE comments',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO comments (id_author, id_blueprint, content, created_at) VALUES (2, 579, 'aze', utc_timestamp()), (2, 1, 'aze', utc_timestamp()), (2, 1, 'aze', utc_timestamp()), (1, 1, 'aze', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (2, 1, 2, 10, 60)',
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (1, 0, 0, 8, 5)',
                ],
                'slug'                 => 'slug_public',
                'user_id'              => null,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_delete'    => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid delete action on unlisted blueprint - user' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    'TRUNCATE TABLE comments',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_unlisted', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO comments (id_author, id_blueprint, content, created_at) VALUES (2, 579, 'aze', utc_timestamp()), (2, 1, 'aze', utc_timestamp()), (1, 1, 'aze', utc_timestamp()), (1, 1, 'aze', utc_timestamp()), (1, 1, 'aze', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (2, 0, 2, 10, 60)',
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment) VALUES (1, 0, 0, 8, 5)',
                ],
                'slug'                 => 'slug_unlisted',
                'user_id'              => 55,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_delete'    => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'csrf incorrect' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                 => 'slug_public',
                'user_id'              => 55,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_delete'    => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'incorrect_csrf',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'missing fields - no csrf' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 55,
                'anonymous_blueprints'  => [1, 2, 3],
                'has_button_delete'     => true,
                'do_post_action'        => true,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">'
                    ]
                ],
            ],
            'do invalid delete action - user has no right' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                 => 'slug_public',
                'user_id'              => 55,
                'anonymous_blueprints' => [5],
                'has_button_delete'    => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error, delete is invalid on this blueprint</div>'
                    ]
                ],
            ],
            'do invalid delete action - visitor has no right' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                 => 'slug_public',
                'user_id'              => null,
                'anonymous_blueprints' => null,
                'has_button_delete'    => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error, delete is invalid on this blueprint</div>'
                    ]
                ],
            ],
            'do invalid delete action - visitor has no right (anonymous_blueprints empty)' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                ],
                'slug'                 => 'slug_public',
                'user_id'              => null,
                'anonymous_blueprints' => null,
                'has_button_delete'    => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-delete_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_blueprint" role="alert">Error, delete is invalid on this blueprint</div>'
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_DeleteBlueprint
     *
     * @param array      $sqlQueries
     * @param string     $slug
     * @param int|null   $userID
     * @param array|null $anonymousBlueprints
     * @param bool       $hasButtonDelete
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
    public function testBlueprintPOSTDeleteBlueprint(array $sqlQueries, string $slug, ?int $userID, ?array $anonymousBlueprints, bool $hasButtonDelete, bool $doPostAction, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages): void
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
            $params['form-delete_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        }

        if ($isFormSuccess) {
            $this->createBlueprintFile('a');
            $this->createBlueprintFile('a', '1.0.9');
            $this->createBlueprintFile('a', '15');
            $this->createBlueprintFile('a', '486485');
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // delete button
        if ($hasButtonDelete) {
            $this->doTestHtmlMain($response, '<button class="form__button form__button--warning" type="submit">Delete blueprint</button>');
        } else {
            $this->doTestHtmlMainNot($response, '<button class="form__button form__button--warning" type="submit">Delete blueprint</button>');
        }

        // stop test if no post action needed
        if ($doPostAction === false) {
            return;
        }

        // get infos
        $countersAuthorBefore = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment FROM users_infos WHERE id_user = 2');
        $countCommentsAuthor = static::$db->count('SELECT COUNT(*) FROM comments WHERE id_author = 2 AND id_blueprint = 1');

        $countersUser1Before = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment FROM users_infos WHERE id_user = 1');
        $countCommentsUser1 = static::$db->count('SELECT COUNT(*) FROM comments WHERE id_author = 1 AND id_blueprint = 1');

        // do post action
        $response = $this->getResponseFromApplication('POST', '/blueprint/' . $slug . '/', $params);

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            if ($isFormSuccess) {
                static::assertSame('/', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/blueprint/' . $slug . '/', $response->getHeaderLine('Location'));
                $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
            }
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        if (!$isFormSuccess) {
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
        }

        // check filesystem -> files has been removed
        if ($isFormSuccess) {
            static::assertSame(1, static::$db->count('SELECT COUNT(*) FROM comments'));

            // file check - must have no file (all versions erased)
            $caracters = \mb_str_split('a');
            $subfolder = '';
            foreach ($caracters as $c) {
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }
            $subfolder = \mb_strtolower($subfolder);

            $storageFolder = \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'storage_test' . \DIRECTORY_SEPARATOR;
            $files = \glob($storageFolder . $subfolder . 'a-*.txt');
            static::assertCount(0, $files);

            $countersAuthorAfter = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment FROM users_infos WHERE id_user = 2');
            $countersUser1After = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment FROM users_infos WHERE id_user = 1');
            if ($slug === 'slug_public') {
                // blueprints author
                static::assertSame((int) $countersAuthorBefore['count_public_blueprint'] - 1, (int) $countersAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersAuthorBefore['count_private_blueprint'] - 1, (int) $countersAuthorAfter['count_private_blueprint']);

                // blueprints user 1 (exist because comments)
                static::assertSame((int) $countersUser1Before['count_public_blueprint'], (int) $countersUser1After['count_public_blueprint']);
                static::assertSame((int) $countersUser1Before['count_private_blueprint'], (int) $countersUser1After['count_private_blueprint']);

                // comments author
                static::assertSame((int) $countersAuthorBefore['count_public_comment'] - $countCommentsAuthor, (int) $countersAuthorAfter['count_public_comment']);
                static::assertSame((int) $countersAuthorBefore['count_private_comment'] - $countCommentsAuthor, (int) $countersAuthorAfter['count_private_comment']);

                // user 1 comments
                static::assertSame((int) $countersUser1Before['count_public_comment'] - $countCommentsUser1, (int) $countersUser1After['count_public_comment']);
                static::assertSame((int) $countersUser1Before['count_private_comment'] - $countCommentsUser1, (int) $countersUser1After['count_private_comment']);
            } else {
                // blueprints
                static::assertSame((int) $countersAuthorBefore['count_public_blueprint'], (int) $countersAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersAuthorBefore['count_private_blueprint'] - 1, (int) $countersAuthorAfter['count_private_blueprint']);

                // blueprints user 1 (exist because comments)
                static::assertSame((int) $countersUser1Before['count_public_blueprint'], (int) $countersUser1After['count_public_blueprint']);
                static::assertSame((int) $countersUser1Before['count_private_blueprint'], (int) $countersUser1After['count_private_blueprint']);

                // comments author
                static::assertSame((int) $countersAuthorBefore['count_public_comment'], (int) $countersAuthorAfter['count_public_comment']);
                static::assertSame((int) $countersAuthorBefore['count_private_comment'] - $countCommentsAuthor, (int) $countersAuthorAfter['count_private_comment']);

                // user 1 comments
                static::assertSame((int) $countersUser1Before['count_public_comment'], (int) $countersUser1After['count_public_comment']);
                static::assertSame((int) $countersUser1Before['count_private_comment'] - $countCommentsUser1, (int) $countersUser1After['count_private_comment']);
            }
        }
    }
}
