<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintPOSTEditCommentTest extends TestCase
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
     * Use for testing edit comment process.
     *
     * @return array[]
     */
    public static function dataCasesBlueprintPOST_EditComment(): array
    {
        return [
            'edit comment OK - public blueprint' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">Your comment has been edited</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['comment'],
                'fieldsLabelError' => [],
            ],
            'edit comment OK - unlisted blueprint' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_unlisted',
                'userID'        => 65,
                'commentID'     => 11,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '11',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">Your comment has been edited</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['comment'],
                'fieldsLabelError' => [],
            ],
            'edit comment OK - private blueprint' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_private',
                'userID'        => 65,
                'commentID'     => 12,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '12',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">Your comment has been edited</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['comment'],
                'fieldsLabelError' => [],
            ],
            'edit comment KO - comments close' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET comments_closed = 1 WHERE id = 966'
                ],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => false,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'edit comment KO - comments hidden' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET comments_hidden = 1 WHERE id = 966'
                ],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => false,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'edit comment KO - ownership incorrect' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 66,
                'commentID'     => 10,
                'hasButtonEdit' => false,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, this comment does not belong to you</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'csrf incorrect' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'incorrect_csrf',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no fields' => [
                'sqlQueries'         => [],
                'slug'               => 'slug_public',
                'userID'             => 65,
                'commentID'          => 10,
                'hasButtonEdit'      => true,
                'params'             => [],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no id' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no comment' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf' => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'   => '10',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - id empty' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => ' ',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - comment empty' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => ['comment'],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [
                    'comment' => 'Comment is required'
                ],
            ],
            'invalid fields - id invalid' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => 'iezhfio',
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">Error, fields are invalid or required</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - id' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => \chr(99999999),
                    'form-edit_comment-textarea-comment' => 'my new comment',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - comment' => [
                'sqlQueries'    => [],
                'slug'          => 'slug_public',
                'userID'        => 65,
                'commentID'     => 10,
                'hasButtonEdit' => true,
                'params'        => [
                    'form-edit_comment-hidden-csrf'      => 'csrf_is_replaced',
                    'form-edit_comment-hidden-id'        => '10',
                    'form-edit_comment-textarea-comment' => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-edit_comment">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-edit_comment" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintPOST_EditComment
     *
     * @param array      $sqlQueries
     * @param string     $slug
     * @param int|null   $userID
     * @param int|null   $commentID
     * @param bool       $hasButtonEdit
     * @param array|null $params
     * @param bool       $useCsrfFromSession
     * @param bool       $hasRedirection
     * @param bool       $isFormSuccess
     * @param array      $flashMessages
     * @param array      $fieldsHasError
     * @param array      $fieldsHasValue
     * @param array      $fieldsLabelError
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesBlueprintPOST_EditComment')]
    public function testBlueprintPOSTEditComment(array $sqlQueries, string $slug, ?int $userID, ?int $commentID, bool $hasButtonEdit, ?array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
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
            $params['form-edit_comment-hidden-csrf'] = $_SESSION['csrf'];
        }

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        $csrf = Security::escAttr($_SESSION['csrf']);
        $htmlEditButton = <<<HTML
<div class="comment__actions">
<form class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this comment?" data-form-confirm-yes="Yes" method="post">
<input name="form-delete_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-delete_comment-hidden-csrf" type="hidden" value="$csrf"/>
<button class="form__button form__button--warning form__button--block_link" type="submit">Delete</button>
</form>
<a class="block__link block__link--no-margin" id="edit_comment-btn-edit-comment-$commentID" href="#">Edit</a>
</div>
HTML;

        // edit button
        if ($hasButtonEdit) {
            $this->doTestHtmlMain($response, $htmlEditButton);
        } else {
            $this->doTestHtmlMainNot($response, $htmlEditButton);
        }

        // get infos
        $blueprintCommentsCountBefore = (int) static::$db->selectVar('SELECT comments_count FROM blueprints WHERE slug = :slug', ['slug' => $slug]);
        $countersUsersBefore = static::$db->selectAll('SELECT count_public_comment, count_private_comment FROM users_infos ORDER BY id_user ASC');
        $commentsBefore = static::$db->selectAll('SELECT * FROM comments');
        $commentBefore = static::$db->selectVar('SELECT content FROM comments WHERE id = :id', ['id' => $commentID]);

        // do post action
        $response = $this->getResponseFromApplication('POST', '/blueprint/' . $slug . '/', $params);

        if ($hasRedirection) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            if ((int) $params['form-edit_comment-hidden-id'] === $commentID && $userID === 65) {
                static::assertSame('/blueprint/' . $slug . '/#comment-' . $commentID, $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/blueprint/' . $slug . '/#comments', $response->getHeaderLine('Location'));
            }
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
        $commentAfter = static::$db->selectVar('SELECT content FROM comments WHERE id = :id', ['id' => $commentID]);

        static::assertEqualsCanonicalizing($countersUsersAfter, $countersUsersBefore);
        static::assertSame($blueprintCommentsCountAfter, $blueprintCommentsCountBefore);

        if ($isFormSuccess) {
            static::assertNotEqualsCanonicalizing($commentsAfter, $commentsBefore);
        } else {
            static::assertEqualsCanonicalizing($commentsAfter, $commentsBefore);
        }

        if (\count($sqlQueries) > 0) {
            return;
        }

        // test fields HTML
        $fields = ['comment'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($userID === 66) {
                // user has not the ownership
                $this->doTestHtmlMainNot($response, '<form data-form-speak-error="Form is invalid:" id="form-edit_comment-' . $commentID . '" method="post">');
                continue;
            }

            if ($field === 'comment') {
                if ($isFormSuccess) {
                    $value = $hasValue ? Helper::trim($params['form-edit_comment-textarea-comment']) : '';
                } elseif (isset($params['form-edit_comment-textarea-comment']) && $params['form-edit_comment-textarea-comment'] === ' ') {
                    $value = '';
                } else {
                    $value = $commentAfter;
                }

                $this->doTestHtmlMain($response, $this->getHTMLFormEditComment($commentID, $csrf, $value, $hasError, $labelError));
            }
        }
    }

    /**
     * @param int    $commentID
     * @param string $csrf
     * @param string $value
     * @param bool   $hasError
     * @param string $labelError
     *
     * @throws SecurityException
     *
     * @return string
     */
    protected function getHTMLFormEditComment(int $commentID, string $csrf, string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);
        // phpcs:disable
        if ($hasError) {
            return <<<HTML
<form data-form-speak-error="Form is invalid:" id="form-edit_comment-$commentID" method="post">
<div class="form__element">
<label class="form__label" for="form-edit_comment-textarea-comment-$commentID" id="form-edit_comment-label-comment-$commentID">Edit comment</label>
<div class="form__container form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-edit_comment-label-comment form-edit_comment-label-comment-error" aria-required="true" class="form__input form__input--textarea form__input--invisible form__input--error" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-edit_comment-textarea-comment-$commentID" name="form-edit_comment-textarea-comment">$v</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-edit_comment-textarea-comment" id="form-edit_comment-label-comment-error">$labelError</label>
</div>
<input name="form-edit_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-edit_comment-hidden-csrf" type="hidden" value="$csrf"/>
<input class="form__button form__button--small" id="form-edit_comment-submit-$commentID" name="form-edit_comment-submit" type="submit" value="Update comment"/>
<input class="form__button form__button--small form__button--secondary" id="edit_comment-btn-cancel_comment-$commentID" type="submit" value="Cancel"/>
HTML;
        }

        return <<<HTML
<form data-form-speak-error="Form is invalid:" id="form-edit_comment-$commentID" method="post">
<div class="form__element">
<label class="form__label" for="form-edit_comment-textarea-comment-$commentID" id="form-edit_comment-label-comment-$commentID">Edit comment</label>
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-edit_comment-label-comment" aria-required="true" class="form__input form__input--textarea form__input--invisible" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-edit_comment-textarea-comment-$commentID" name="form-edit_comment-textarea-comment">$v</textarea>
<span class="form__feedback"></span>
</div>
</div>
<input name="form-edit_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-edit_comment-hidden-csrf" type="hidden" value="$csrf"/>
<input class="form__button form__button--small" id="form-edit_comment-submit-$commentID" name="form-edit_comment-submit" type="submit" value="Update comment"/>
<input class="form__button form__button--small form__button--secondary" id="edit_comment-btn-cancel_comment-$commentID" type="submit" value="Cancel"/>
HTML;
        // phpcs:enable
    }
}
