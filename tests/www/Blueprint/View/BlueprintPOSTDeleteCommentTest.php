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
use Rancoud\Security\Security;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintPOSTDeleteCommentTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user
        $sql = <<<SQL
            REPLACE INTO users (`id`, `username`, `slug`, `grade`, `created_at`)
            VALUES (65, 'user_65', 'user_65', 'member', utc_timestamp()),
                   (66, 'user_66', 'user_66', 'member', utc_timestamp())
        SQL;
        static::$db->exec($sql);

        // user infos
        $sql = <<<SQL
            REPLACE INTO users_infos (id_user, count_public_comment, count_private_comment)
            VALUES (65, 1, 3),
                   (66, 0, 0)
        SQL;
        static::$db->exec($sql);

        // blueprints
        $sql = <<<SQL
            REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure)
            VALUES (966, 65, 'slug_public',   'a1', 'my title 1', 1, utc_timestamp(), utc_timestamp(), 'public'),
                   (967, 65, 'slug_unlisted', 'a2', 'my title 2', 1, utc_timestamp(), utc_timestamp(), 'unlisted'),
                   (968, 65, 'slug_private',  'a3', 'my title 3', 1, utc_timestamp(), utc_timestamp(), 'private')
        SQL;
        static::$db->exec($sql);

        // blueprints version
        $sql = <<<SQL
            REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at)
            VALUES (966, 1, 'First commit', utc_timestamp(), utc_timestamp()),
                   (967, 1, 'First commit', utc_timestamp(), utc_timestamp()),
                   (968, 1, 'First commit', utc_timestamp(), utc_timestamp())
        SQL;
        static::$db->exec($sql);
    }

    /**
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        // comment
        $sql = <<<SQL
            REPLACE INTO comments (id, id_author, id_blueprint, content, created_at)
            VALUES (10, 65, 966, 'com 1 public', utc_timestamp()),
                   (11, 65, 967, 'com 1 unlisted', utc_timestamp()),
                   (12, 65, 968, 'com 1 private', utc_timestamp())
        SQL;
        static::$db->exec($sql);

        // user infos
        $sql = <<<SQL
            REPLACE INTO users_infos (id_user, count_public_comment, count_private_comment)
            VALUES (65, 1, 3),
                   (66, 0, 0)
        SQL;
        static::$db->exec($sql);

        // blueprints
        $sql = <<<SQL
            REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, comments_count, comments_closed, comments_hidden)
            VALUES (966, 65, 'slug_public',   'a1', 'my title 1', 1, utc_timestamp(), utc_timestamp(), 'public', 1, 0, 0),
                   (967, 65, 'slug_unlisted', 'a2', 'my title 2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 1, 0, 0),
                   (968, 65, 'slug_private',  'a3', 'my title 3', 1, utc_timestamp(), utc_timestamp(), 'private', 1, 0, 0)
        SQL;
        static::$db->exec($sql);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing delete comment process.
     *
     * @return array[]
     */
    public static function dataCasesBlueprintPOST_DeleteComment(): array
    {
        return [
            'delete comment OK - public blueprint' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">Your comment has been deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'delete comment OK - unlisted blueprint' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_unlisted',
                'userID'          => 65,
                'commentID'       => 11,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '11',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">Your comment has been deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'delete comment OK - private blueprint' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_private',
                'userID'          => 65,
                'commentID'       => 12,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '12',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">Your comment has been deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'delete comment KO - comments close' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET comments_closed = 1 WHERE id = 966'
                ],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => false,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'delete comment KO - comments hidden' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET comments_hidden = 1 WHERE id = 966'
                ],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => false,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'delete comment KO - ownership incorrect' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 66,
                'commentID'       => 10,
                'hasButtonDelete' => false,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">Error, this comment does not belong to you</div>'
                    ]
                ],
            ],
            'csrf incorrect' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'incorrect_csrf',
                    'form-delete_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'missing fields - no fields' => [
                'sqlQueries'         => [],
                'slug'               => 'slug_public',
                'userID'             => 65,
                'commentID'          => 10,
                'hasButtonDelete'    => true,
                'params'             => [],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'missing fields - no csrf' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-id' => '10',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
            'missing fields - no id' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">Error, missing fields</div>'
                    ]
                ],
            ],
            'empty fields - id empty' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
            ],
            'invalid fields - id invalid' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => 'iezhfio',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
            ],
            'invalid encoding fields - id' => [
                'sqlQueries'      => [],
                'slug'            => 'slug_public',
                'userID'          => 65,
                'commentID'       => 10,
                'hasButtonDelete' => true,
                'params'          => [
                    'form-delete_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-delete_comment-hidden-id'   => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-delete_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-delete_comment" role="alert">'
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_DeleteComment
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Rancoud\Security\SecurityException
     */
    #[DataProvider('dataCasesBlueprintPOST_DeleteComment')]
    public function testBlueprintPOSTDeleteComment(array $sqlQueries, string $slug, ?int $userID, ?int $commentID, bool $hasButtonDelete, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user in $_SESSION
        $session = ['remove' => [], 'set' => []];
        if ($userID !== null) {
            $session['set']['userID'] = $userID;
        } else {
            $session['remove'][] = 'userID';
        }

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-delete_comment-hidden-csrf'] = $_SESSION['csrf'];
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        $csrf = Security::escAttr($_SESSION['csrf']);
        $html = <<<HTML
<div class="comment__actions">
<form class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this comment?" data-form-confirm-yes="Yes" method="post">
<input name="form-delete_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-delete_comment-hidden-csrf" type="hidden" value="$csrf"/>
<button class="form__button form__button--warning form__button--block_link" type="submit">Delete</button>
</form>
<a class="block__link block__link--no-margin" id="edit_comment-btn-edit-comment-$commentID" href="#">Edit</a>
</div>
HTML;

        // delete button
        if ($hasButtonDelete) {
            $this->doTestHtmlMain($response, $html);
        } else {
            $this->doTestHtmlMainNot($response, $html);
        }

        // get infos
        $blueprintCommentsCountBefore = (int) static::$db->selectVar('SELECT comments_count FROM blueprints WHERE slug = :slug', ['slug' => $slug]);
        $countersUsersBefore = static::$db->selectAll('SELECT count_public_comment, count_private_comment FROM users_infos ORDER BY id_user ASC');
        $commentsBefore = static::$db->selectAll('SELECT * FROM comments');

        // do post action
        $response = $this->getResponseFromApplication('POST', '/blueprint/' . $slug . '/', $params);

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            static::assertSame('/blueprint/' . $slug . '/#comments', $response->getHeaderLine('Location'));
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

        $blueprintCommentsCountAfter = (int) static::$db->selectVar('SELECT comments_count FROM blueprints WHERE slug = :slug', ['slug' => $slug]);
        $countersUsersAfter = static::$db->selectAll('SELECT count_public_comment, count_private_comment FROM users_infos ORDER BY id_user ASC');
        $commentsAfter = static::$db->selectAll('SELECT * FROM comments');

        if ($isFormSuccess) {
            static::assertCount(2, $commentsAfter);
            static::assertSame($blueprintCommentsCountAfter, $blueprintCommentsCountBefore - 1);

            static::assertEqualsCanonicalizing($countersUsersAfter[1], $countersUsersBefore[1]);

            if ($slug === 'slug_public') {
                static::assertSame((int) $countersUsersAfter[0]['count_public_comment'], (int) $countersUsersBefore[0]['count_public_comment'] - 1);
                static::assertSame((int) $countersUsersAfter[0]['count_private_comment'], (int) $countersUsersBefore[0]['count_private_comment'] - 1);
            } else {
                static::assertSame((int) $countersUsersAfter[0]['count_public_comment'], (int) $countersUsersBefore[0]['count_public_comment']);
                static::assertSame((int) $countersUsersAfter[0]['count_private_comment'], (int) $countersUsersBefore[0]['count_private_comment'] - 1);
            }
        } else {
            static::assertCount(3, $commentsAfter);
            static::assertEqualsCanonicalizing($countersUsersAfter, $countersUsersBefore);
            static::assertSame($blueprintCommentsCountAfter, $blueprintCommentsCountBefore);
        }
    }
}
