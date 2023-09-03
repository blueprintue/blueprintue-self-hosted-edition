<?php

/* @noinspection HtmlUnknownTarget */
/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use app\helpers\Helper;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintGETCommentsTest extends TestCase
{
    use Common;

    protected static array $users = [
        1 => ['username' => 'use<script>alert(1)</script>r_1', 'slug' => '/profile/user_<script>alert(1)</script>1/', 'avatar' => null],
        3 => ['username' => 'use<script>alert(1)</script>r_3', 'slug' => '/profile/user_<script>alert(1)</script>3/', 'avatar' => null],
        4 => ['username' => 'use<script>alert(1)</script>r_4', 'slug' => '/profile/user_<script>alert(1)</script>4/', 'avatar' => '/medias/avatars/pict<script>alert(1)</script>ure'],
    ];
    protected static array $comments = [
        1 => ['id_author' => 1, 'name_fallback' => null, 'content' => 'comment_content_1', 'date' => ''],
        2 => ['id_author' => 3, 'name_fallback' => null, 'content' => 'comment_content_2', 'date' => ''],
        3 => ['id_author' => 4, 'name_fallback' => null, 'content' => 'comment_content_3', 'date' => ''],
        4 => ['id_author' => 1, 'name_fallback' => null, 'content' => 'comment_content_4', 'date' => ''],
        5 => ['id_author' => 3, 'name_fallback' => null, 'content' => 'comment_content_5', 'date' => ''],
        6 => ['id_author' => null, 'name_fallback' => 'fallback <script>alert(1)</script> name here', 'content' => 'comment_content_6', 'date' => ''],
    ];

    /**
     * @throws DatabaseException
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        $formattedDates = [];
        for ($i = 0; $i < 10; ++$i) {
            $formattedDates['-' . $i . ' days'] = (new DateTime('now', new DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s');
        }

        // blueprints
        static::$db->exec("INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version, comments_count) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12', 0)");
        static::$db->exec("INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())");

        // users
        $sqlUsers = <<<SQL
            INSERT INTO users (id, username, password, slug, email, created_at, avatar)
            VALUES (1, 'use<script>alert(1)</script>r_1', null, 'user_<script>alert(1)</script>1', 'user_1@mail', utc_timestamp(), NULL),
                   (3, 'use<script>alert(1)</script>r_3', null, 'user_<script>alert(1)</script>3', 'user_3@mail', utc_timestamp(), NULL),
                   (4, 'use<script>alert(1)</script>r_4', null, 'user_<script>alert(1)</script>4', 'user_4@mail', utc_timestamp(), 'pict<script>alert(1)</script>ure')
        SQL;
        static::$db->exec($sqlUsers);

        // comments
        static::$comments[1]['date'] = $formattedDates['-9 days'];
        static::$comments[2]['date'] = $formattedDates['-8 days'];
        static::$comments[3]['date'] = $formattedDates['-7 days'];
        static::$comments[4]['date'] = $formattedDates['-6 days'];
        static::$comments[5]['date'] = $formattedDates['-5 days'];
        static::$comments[6]['date'] = $formattedDates['-4 days'];
        $sqlComments = <<<SQL
            INSERT INTO comments (id, id_author, id_blueprint, name_fallback, content, created_at)
            VALUES (1,    1, 99, NULL,                 'comment_content_1', '{$formattedDates['-9 days']}'),
                   (2,    3, 99, NULL,                 'comment_content_2', '{$formattedDates['-8 days']}'),
                   (3,    4, 99, NULL,                 'comment_content_3', '{$formattedDates['-7 days']}'),
                   (4,    1, 99, NULL,                 'comment_content_4', '{$formattedDates['-6 days']}'),
                   (5,    3, 99, NULL,                 'comment_content_5', '{$formattedDates['-5 days']}'),
                   (6, NULL, 99, 'fallback <script>alert(1)</script> name here', 'comment_content_6', '{$formattedDates['-4 days']}')
        SQL;
        static::$db->exec($sqlComments);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing list blueprint's comments.
     *
     * @throws \Exception
     *
     * @return array[]
     */
    public function dataCasesBlueprintGET_CommentsBlueprint(): array
    {
        return [
            'visitor - 0 comments - no form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0'
                ],
                'slug'               => 'slug_public',
                'user_id'            => null,
                'has_title'          => false,
                'count_comments'     => 0,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [],
            ],
            'visitor - 1 comments - no form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 1'
                ],
                'slug'               => 'slug_public',
                'user_id'            => null,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [[1, 1]],
            ],
            'user - 0 comments - has form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => true,
                'count_comments'     => 0,
                'has_form'           => true,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [],
            ],
            'user - 1 comments - has form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 2',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => true,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [[2, 3]],
            ],
            'user - 0 comments - no form - comments closed' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 0, comments_closed = 1 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => false,
                'count_comments'     => 0,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => true,
                'comment_ids'        => [],
            ],
            'user - 1 comments - no form - comments closed' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 1 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 3',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => true,
                'comment_ids'        => [[3, 4]],
            ],
            'user - 0 comments - no form - comments hidden' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 1, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => false,
                'count_comments'     => 0,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => true,
                'is_comments_closed' => false,
                'comment_ids'        => [],
            ],
            'user - 1 comments - no form - comments hidden' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 1, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 4',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 55,
                'has_title'          => false,
                'count_comments'     => 1,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => true,
                'is_comments_closed' => false,
                'comment_ids'        => [[4, 1]],
            ],
            'author - 0 comments - has form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => true,
                'count_comments'     => 0,
                'has_form'           => true,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [],
            ],
            'author - 1 comments - has form' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 5',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => true,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [[5, 3]],
            ],
            'author - 1 comments - has form - has edit' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 1',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => true,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => false,
                'comment_ids'        => [[1, 1]],
            ],
            'author - 0 comments - no form - comments closed' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 0, comments_closed = 1 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => false,
                'count_comments'     => 0,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => true,
                'comment_ids'        => [],
            ],
            'author - 1 comments - no form - comments closed' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 0, comments_closed = 1 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 6',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => true,
                'count_comments'     => 1,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => false,
                'is_comments_closed' => true,
                'comment_ids'        => [[6, null]],
            ],
            'author - 0 comments - no form - comments hidden' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 0, comments_hidden = 1, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => false,
                'count_comments'     => 0,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => true,
                'is_comments_closed' => false,
                'comment_ids'        => [],
            ],
            'author - 1 comments - no form - comments hidden' => [
                'sql_queries' => [
                    'UPDATE blueprints SET comments_count = 1, comments_hidden = 1, comments_closed = 0 WHERE id = 1',
                    'UPDATE comments SET id_blueprint = 0 WHERE id > 0',
                    'UPDATE comments SET id_blueprint = 1 WHERE id = 1',
                ],
                'slug'               => 'slug_public',
                'user_id'            => 1,
                'has_title'          => false,
                'count_comments'     => 1,
                'has_form'           => false,
                'page_url'           => '/blueprint/slug_public/',
                'is_comments_hidden' => true,
                'is_comments_closed' => false,
                'comment_ids'        => [[1, 1]],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesBlueprintGET_CommentsBlueprint
     *
     * @param array    $sqlQueries
     * @param string   $slug
     * @param int|null $userID
     * @param bool     $hasTitle
     * @param int      $countComments
     * @param bool     $hasForm
     * @param string   $pageURL
     * @param bool     $isCommentsHidden
     * @param bool     $isCommentsClosed
     * @param array    $commentIDs
     *
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     * @throws \Exception
     */
    public function testBlueprintGETCommentsBlueprint(array $sqlQueries, string $slug, ?int $userID, bool $hasTitle, int $countComments, bool $hasForm, string $pageURL, bool $isCommentsHidden, bool $isCommentsClosed, array $commentIDs): void
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

        // init session
        $this->getResponseFromApplication('GET', '/', [], $session);

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        $wordComment = 'comment';
        if ($countComments > 1) {
            $wordComment = 'comments';
        }

        if ($hasTitle) {
            $this->doTestHtmlMain($response, '<h2 class="block__title" id="comments">' . $countComments . ' <span class="block__title--emphasis">' . $wordComment . '</span></h2>');
        } else {
            $this->doTestHtmlMainNot($response, '<h2 class="block__title" id="comments">' . $countComments . ' <span class="block__title--emphasis">' . $wordComment . '</span></h2>');
        }

        if ($hasForm) {
            $this->doTestHtmlMain($response, '<form action="' . Security::escAttr($pageURL . '#comments') . '" data-form-speak-error="Form is invalid:" id="form-add_comment" method="post">');
        } else {
            $this->doTestHtmlMainNot($response, '<form action="' . Security::escAttr($pageURL . '#comments') . '" data-form-speak-error="Form is invalid:" id="form-add_comment" method="post">');
        }

        if ($isCommentsHidden) {
            $this->doTestHtmlMainNot($response, '<h2 class="block__title" id="comments">' . $countComments . ' <span class="block__title--emphasis">' . $wordComment . '</span></h2>');
            $this->doTestHtmlMain($response, '<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">');
            $this->doTestHtmlMainNot($response, '<ul class="comment__list">');
        }

        if ($isCommentsClosed) {
            $this->doTestHtmlMainNot($response, '<form action="' . Security::escAttr($pageURL . '#comments') . '" data-form-speak-error="Form is invalid:" id="form-add_comment" method="post">');
            if ($countComments > 0) {
                $this->doTestHtmlMain($response, '<div class="block__container block__container--white-grey block__container--shadow-top">');
            } else {
                $this->doTestHtmlMain($response, '<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">');
            }
        }

        if ($isCommentsHidden) {
            return;
        }

        if ($countComments === 0) {
            $this->doTestHtmlMainNot($response, '<ul class="comment__list">');
        } elseif ($countComments === 1) {
            $this->doTestHtmlMain($response, '<ul class="comment__list">');
            $this->doTestHtmlMain($response, $this->getLiHTML($userID, $isCommentsClosed, $commentIDs[0]));
        }
    }

    /**
     * @throws \Exception
     */
    protected function getLiHTML(?int $userID, bool $isCommentsClosed, array $commentIDs): string
    {
        [$commentID, $commentAuthorID] = $commentIDs;

        $comment = static::$comments[$commentID];
        if ($commentAuthorID !== null) {
            $user = static::$users[$commentAuthorID];
            $username = Security::escHTML($user['username']);
            $userSlug = Security::escAttr($user['slug']);
        } else {
            $username = Security::escHTML($comment['name_fallback']);
            $user = ['avatar' => null];
        }
        $dateComment = Helper::formatDate($comment['date'], 'F j, Y, g:i a');
        $content = $comment['content'];

        // region avatar HTML
        $avatarHTML = '';
        if ($user['avatar'] !== null) {
            $avatar = Security::escAttr($user['avatar']);
            $avatarHTML = <<<HTML
<img alt="avatar author" class="blueprint__avatar-container" src="$avatar" />
HTML;
        } else {
            $avatarHTML = <<<HTML
<div class="blueprint__avatar-container blueprint__avatar-container--background">
<svg class="blueprint__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
HTML;
        }
        // endregion

        // region author HTML
        $authorHTML = '';
        if ($comment['id_author'] !== null) {
            $authorHTML = <<<HTML
<div class="comment__author">
<h2 class="blueprint__author"><a class="blueprint__profile" href="$userSlug">$username</a></h2>
<p class="blueprint__time">$dateComment</p>
</div>
HTML;
        } else {
            $authorHTML = <<<HTML
<div class="comment__author">
<h2 class="blueprint__author">$username</h2>
<p class="blueprint__time">$dateComment</p>
</div>
HTML;
        }
        // endregion

        // phpcs:disable
        if ($userID !== $commentAuthorID || $isCommentsClosed) {
            return <<<HTML
<ul class="comment__list">
<li class="comment__item" id="comment-$commentID">
<div class="blueprint__author-infos">
$avatarHTML
$authorHTML
</div>
<div class="comment__content"><p>$content</p></div>
</li>
</ul>
HTML;
        }

        // has edit buttons / form

        $csrf = Security::escAttr($_SESSION['csrf']);

        return <<<HTML
<ul class="comment__list">
<li class="comment__item" data-edit_comment data-edit_comment-btn_cancel_id="edit_comment-btn-cancel_comment-$commentID" data-edit_comment-btn_id="edit_comment-btn-edit-comment-$commentID" data-edit_comment-content_id="edit_comment-content-$commentID" data-edit_comment-edit_content_id="edit_comment-edit_content-$commentID" id="comment-$commentID">
<div class="blueprint__author-infos">
$avatarHTML
$authorHTML
<div class="comment__actions">
<form class="form__inline" data-form-confirm data-form-confirm-no="No" data-form-confirm-question="Are you sure you want to delete this comment?" data-form-confirm-yes="Yes" method="post">
<input name="form-delete_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-delete_comment-hidden-csrf" type="hidden" value="$csrf"/>
<button class="form__button form__button--warning form__button--block_link" type="submit">Delete</button>
</form>
<a class="block__link block__link--no-margin" id="edit_comment-btn-edit-comment-$commentID" href="#">Edit</a>
</div>
</div>
<div class="comment__content" id="edit_comment-content-$commentID"><p>$content</p></div>
<div class="comment__content comment__hide" id="edit_comment-edit_content-$commentID">
<form data-form-speak-error="Form is invalid:" id="form-edit_comment-$commentID" method="post">
<div class="form__element">
<label class="form__label" for="form-edit_comment-textarea-comment-$commentID" id="form-edit_comment-label-comment-$commentID">Edit comment</label>
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-edit_comment-label-comment" aria-required="true" class="form__input form__input--textarea form__input--invisible" data-form-error-required="Comment is required" data-form-has-container data-form-rules="required" id="form-edit_comment-textarea-comment-$commentID" name="form-edit_comment-textarea-comment">comment_content_1</textarea>
<span class="form__feedback"></span>
</div>
</div>
<input name="form-edit_comment-hidden-id" type="hidden" value="$commentID"/>
<input name="form-edit_comment-hidden-csrf" type="hidden" value="$csrf"/>
<input class="form__button form__button--small" id="form-edit_comment-submit-$commentID" name="form-edit_comment-submit" type="submit" value="Update comment"/>
<input class="form__button form__button--small form__button--secondary" id="edit_comment-btn-cancel_comment-$commentID" type="submit" value="Cancel"/>
</form>
</div>
</li>
</ul>
HTML;
        // phpcs:enable
    }
}
