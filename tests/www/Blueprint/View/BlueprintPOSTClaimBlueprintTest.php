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

class BlueprintPOSTClaimBlueprintTest extends TestCase
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
     * Use for testing claim blueprint process.
     *
     * @return array[]
     */
    public function dataCasesBlueprintPOST_ClaimBlueprint(): array
    {
        return [
            'visitor - no button claim' => [
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
                'has_button_claim'      => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'user - no button claim' => [
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
                'has_button_claim'      => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'author - no button claim' => [
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
                'has_button_claim'      => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'user who post as anonymous - has button claim' => [
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
                'has_button_claim'      => true,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'user who post as anonymous then claim but $_SESSION still have the references - no button claim' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                  => 'slug_public',
                'user_id'               => 1,
                'anonymous_blueprints'  => [1, 2, 3],
                'has_button_claim'      => false,
                'do_post_action'        => false,
                'params'                => null,
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid claim action on public blueprint' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member', null, 'member', 'member@mail', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (55, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint) VALUES (2, 1, 1)',
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint) VALUES (55, 0, 0)',
                ],
                'slug'                 => 'slug_public',
                'user_id'              => 55,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_claim'     => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">This blueprint is now yours</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'do valid claim action on unlisted blueprint' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_unlisted', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member', null, 'member', 'member@mail', utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (55, 'member2', null, 'member2', 'member2@mail', utc_timestamp())",
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint) VALUES (2, 0, 1)',
                    'REPLACE INTO users_infos (id_user, count_public_blueprint, count_private_blueprint) VALUES (55, 0, 0)',
                ],
                'slug'                 => 'slug_unlisted',
                'user_id'              => 55,
                'anonymous_blueprints' => [1, 2, 3],
                'has_button_claim'     => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">This blueprint is now yours</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
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
                'has_button_claim'     => true,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'incorrect_csrf',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
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
                'has_button_claim'      => true,
                'do_post_action'        => true,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
            'do invalid claim action - anonymous blueprints is not listed' => [
                'sql_queries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (2, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                    "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (2, 'member', null, 'member', 'member@mail', utc_timestamp())",
                ],
                'slug'                 => 'slug_public',
                'user_id'              => 55,
                'anonymous_blueprints' => [5],
                'has_button_claim'     => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">Error, claim is invalid on this blueprint</div>'
                    ]
                ],
            ],
            'do invalid claim action - only possible on anonymous user' => [
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
                'has_button_claim'     => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">Error, claim is invalid on this blueprint</div>'
                    ]
                ],
            ],
            'do invalid claim action - visitor try to hack' => [
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
                'has_button_claim'     => false,
                'do_post_action'       => true,
                'params'               => [
                    'form-claim_blueprint-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-claim_blueprint">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-claim_blueprint" role="alert">'
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_ClaimBlueprint
     *
     * @param array      $sqlQueries
     * @param string     $slug
     * @param int|null   $userID
     * @param array|null $anonymousBlueprints
     * @param bool       $hasButtonClaim
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
    public function testBlueprintPOSTClaimBlueprint(array $sqlQueries, string $slug, ?int $userID, ?array $anonymousBlueprints, bool $hasButtonClaim, bool $doPostAction, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages): void
    {
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
            $params['form-claim_blueprint-hidden-csrf'] = $_SESSION['csrf'];
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        // claim button
        if ($hasButtonClaim) {
            $this->doTestHtmlMain($response, '<button class="form__button" type="submit">Claim blueprint</button>');
        } else {
            $this->doTestHtmlMainNot($response, '<button class="form__button" type="submit">Claim blueprint</button>');
        }

        // stop test if no post action needed
        if ($doPostAction === false) {
            return;
        }

        // get infos
        $countersOldAuthorBefore = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint FROM users_infos WHERE id_user = 2');
        $countersNewAuthorBefore = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint FROM users_infos WHERE id_user = 55');

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
            $countersOldAuthorAfter = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint FROM users_infos WHERE id_user = 2');
            $countersNewAuthorAfter = static::$db->selectRow('SELECT count_public_blueprint, count_private_blueprint FROM users_infos WHERE id_user = 55');
            if ($slug === 'slug_public') {
                static::assertSame((int) $countersOldAuthorBefore['count_public_blueprint'] - 1, (int) $countersOldAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersOldAuthorBefore['count_private_blueprint'] - 1, (int) $countersOldAuthorAfter['count_private_blueprint']);

                static::assertSame((int) $countersNewAuthorBefore['count_public_blueprint'] + 1, (int) $countersNewAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersNewAuthorBefore['count_private_blueprint'] + 1, (int) $countersNewAuthorAfter['count_private_blueprint']);
            } else {
                static::assertSame((int) $countersOldAuthorBefore['count_public_blueprint'], (int) $countersOldAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersOldAuthorBefore['count_private_blueprint'] - 1, (int) $countersOldAuthorAfter['count_private_blueprint']);

                static::assertSame((int) $countersNewAuthorBefore['count_public_blueprint'], (int) $countersNewAuthorAfter['count_public_blueprint']);
                static::assertSame((int) $countersNewAuthorBefore['count_private_blueprint'] + 1, (int) $countersNewAuthorAfter['count_private_blueprint']);
            }
        }
    }
}
