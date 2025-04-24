<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\CommentService;
use app\services\www\TagService;
use app\services\www\UserService;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Session\Session;

class BlueprintController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected ?int $userID = null;

    protected array $inputs = [
        'delete_blueprint' => [
            'CSRF'    => 'form-delete_blueprint-hidden-csrf',
        ],
        'claim_blueprint' => [
            'CSRF'    => 'form-claim_blueprint-hidden-csrf',
        ],
        'delete_version_blueprint' => [
            'CSRF'    => 'form-delete_version_blueprint-hidden-csrf',
            'version' => 'form-delete_version_blueprint-hidden-version',
        ],
        'add_comment' => [
            'CSRF'    => 'form-add_comment-hidden-csrf',
            'comment' => 'form-add_comment-textarea-comment',
        ],
        'edit_comment' => [
            'CSRF'    => 'form-edit_comment-hidden-csrf',
            'id'      => 'form-edit_comment-hidden-id',
            'comment' => 'form-edit_comment-textarea-comment',
        ],
        'delete_comment' => [
            'CSRF' => 'form-delete_comment-hidden-csrf',
            'id'   => 'form-delete_comment-hidden-id',
        ]
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'blueprint';
        $this->currentPageForNavBar = 'blueprint';

        $this->url = Helper::getHostname() . $data['url'];

        $this->title = $data['title'] . ' posted by ' . $data['author'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $description = Helper::getFitSentence($data['description'] ?? '', 255);
        $this->description = ($description !== '') ? $description : 'No description provided';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->userID = Session::get('userID');

        // access
        $blueprint = $this->getBlueprint($request);
        if ($blueprint === null) {
            return $this->redirect('/');
        }

        $blueprint = $this->getBlueprintVersions($request, $blueprint);
        if ($blueprint === null) {
            return $this->redirect('/');
        }

        // treat forms
        if ($this->hasSentForm($request, 'POST', $this->inputs['delete_blueprint'], 'error-form-delete_blueprint')) {
            return $this->doProcessDeleteBlueprint($blueprint, $blueprint['page_url']);
        }

        if ($this->userID !== null) {
            if ($this->hasSentForm($request, 'POST', $this->inputs['claim_blueprint'], 'error-form-claim_blueprint')) {
                return $this->doProcessClaimBlueprint($blueprint);
            }

            if ($this->hasSentForm($request, 'POST', $this->inputs['delete_version_blueprint'], 'error-form-delete_version_blueprint')) {
                $cleanedParams = $this->treatFormDeleteVersion($request);

                return $this->doProcessDeleteVersionBlueprint($blueprint, $cleanedParams);
            }

            if ($blueprint['comments_hidden'] === 0 && $blueprint['comments_closed'] === 0) {
                if ($this->hasSentForm($request, 'POST', $this->inputs['add_comment'], 'error-form-add_comment')) {
                    $cleanedParams = $this->treatFormAddComment($request);

                    return $this->doProcessAddComment($blueprint, $cleanedParams);
                }

                if ($this->hasSentForm($request, 'POST', $this->inputs['edit_comment'], 'error-form-edit_comment')) {
                    $cleanedParams = $this->treatFormEditComment($request);

                    return $this->doProcessEditComment($blueprint, $cleanedParams);
                }

                if ($this->hasSentForm($request, 'POST', $this->inputs['delete_comment'], 'error-form-delete_comment')) {
                    $cleanedParams = $this->treatFormDeleteComment($request);

                    return $this->doProcessDeleteComment($blueprint, $cleanedParams);
                }
            }
        }

        // authorizations
        $anonymousBlueprints = Session::get('anonymous_blueprints') ?? [];
        $isAuthor = ($this->userID === $blueprint['id_author']);
        $canClaim = false;
        $canDelete = false;
        $this->isToHideFromGoogle = ($blueprint['exposure'] !== 'public');

        if (!$isAuthor && \in_array($blueprint['id'], $anonymousBlueprints, true)) {
            $canDelete = true;
            if ($this->userID !== null) {
                $canClaim = true;
            }
        }

        // comments
        $hasOwnComments = false;
        $comments = null;
        if ($blueprint['comments_hidden'] === 0) {
            $comments = CommentService::getAllCommentsWithBlueprintID($blueprint['id']);
        }

        $users = UserService::getInfosFromIdAuthorIndex(\array_merge([$blueprint], $comments ?? []));

        $hasCommentActions = ($blueprint['comments_hidden'] === 0 && $blueprint['comments_closed'] === 0);
        if ($comments !== null) {
            foreach ($comments as $key => $comment) {
                $comments[$key]['created_at'] = Helper::formatDate($comment['created_at'], 'F j, Y, g:i a');

                if ($comment['id_author'] === null) {
                    $comments[$key]['author']['avatar_url'] = null;
                    $comments[$key]['can_edit'] = false;
                    continue;
                }

                $comments[$key]['author'] = Helper::formatUser($users[$comment['id_author']]);
                $comments[$key]['can_edit'] = ($hasCommentActions && $this->userID !== null && $comments[$key]['author']['id'] === $this->userID);

                if ($comments[$key]['can_edit']) {
                    $hasOwnComments = true;
                }
            }
        }

        // template properties
        $this->setTemplateProperties([
            'url'         => $blueprint['page_url'],
            'title'       => $blueprint['title'],
            'author'      => $users[$blueprint['id_author']]['username'],
            'description' => $blueprint['description']
        ]);

        // data for page
        $blueprintData = [
            'author'            => Helper::formatUser($users[$blueprint['id_author']]),
            'title'             => $blueprint['title'],
            'type'              => $blueprint['type'],
            'ue_version'        => $blueprint['ue_version'],
            'versions'          => Helper::organizeVersionHistoryForDisplay($blueprint['slug'], $blueprint['versions']),
            'thumbnail_url'     => Helper::getThumbnailUrl($blueprint['thumbnail']),
            'description'       => $blueprint['description'],
            'published_at'      => Helper::getDateFormattedWithUserTimezone($blueprint['published_at'], 'F j, Y, g:i a'),
            'exposure'          => $blueprint['exposure'],
            'expiration'        => Helper::getTimeleft($blueprint['expiration']),
            'tags'              => TagService::getTagsWithListIDs($blueprint['tags']),
            'video'             => $blueprint['video'],
            'content'           => BlueprintService::getBlueprintContent($blueprint['file_id'], $blueprint['current_version']),
            'embed_url'         => '<iframe src="' . Helper::getHostname() . $blueprint['render_url'] . '" scrolling="no" allowfullscreen></iframe>',
            'edit_url'          => Application::getRouter()->generateUrl('blueprint-edit', ['blueprint_slug' => $blueprint['slug']]),
            'comments'          => $comments,
            'video_provider'    => $blueprint['video_provider'],
            'video_privacy_url' => Helper::getVideoPrivacyURL($blueprint['video_provider']),
            'comments_hidden'   => ($blueprint['comments_hidden'] === 1),
            'comments_closed'   => ($blueprint['comments_closed'] === 1),
            'comments_count'    => $blueprint['comments_count'],
        ];
        $this->data += ['markdown' => (new Parsedown())->setSafeMode(true)];
        $this->data += ['can_edit' => $isAuthor];
        $this->data += ['can_comment' => ($this->userID !== null)];
        $this->data += ['can_claim' => $canClaim];
        $this->data += ['can_delete' => $canDelete];
        $this->data += ['has_own_comments' => $hasOwnComments];
        $this->data += ['page_url' => $blueprint['page_url']];
        $this->data += ['blueprint' => $blueprintData];

        if ($this->data['blueprint']['comments_closed'] === true && empty($this->data['blueprint']['comments'])) {
            $this->data['blueprint']['comments_hidden'] = true;
        }

        if ($this->data['blueprint']['comments'] === null && $this->userID === null) {
            $this->data['blueprint']['comments_hidden'] = true;
        }

        $csrf = Session::get('csrf');

        $this->data += [$this->inputs['delete_blueprint']['CSRF'] => $csrf];
        $this->data += [$this->inputs['claim_blueprint']['CSRF'] => $csrf];
        $this->data += [$this->inputs['delete_version_blueprint']['CSRF'] => $csrf];
        $this->data += [$this->inputs['add_comment']['CSRF'] => $csrf];
        $this->data += [$this->inputs['edit_comment']['CSRF'] => $csrf];
        $this->data += [$this->inputs['delete_comment']['CSRF'] => $csrf];

        $formDeleteBlueprint = new FormHelper();
        $formDeleteBlueprint->setErrorMessage(Session::getFlash('error-form-delete_blueprint'));
        $this->data += ['form-delete_blueprint' => $formDeleteBlueprint];

        $formClaimBlueprint = new FormHelper();
        $formClaimBlueprint->setErrorMessage(Session::getFlash('error-form-claim_blueprint'));
        $formClaimBlueprint->setSuccessMessage(Session::getFlash('success-form-claim_blueprint'));
        $this->data += ['form-claim_blueprint' => $formClaimBlueprint];

        $formDeleteVersionBlueprint = new FormHelper();
        $formDeleteVersionBlueprint->setErrorMessage(Session::getFlash('error-form-delete_version_blueprint'));
        $formDeleteVersionBlueprint->setSuccessMessage(Session::getFlash('success-form-delete_version_blueprint'));
        $this->data += ['form-delete_version_blueprint' => $formDeleteVersionBlueprint];

        $formAddComment = new FormHelper();
        $formAddComment->setInputsValues(Session::getFlash('form-add_comment-values'));
        $formAddComment->setInputsErrors(Session::getFlash('form-add_comment-errors'));
        $formAddComment->setErrorMessage(Session::getFlash('error-form-add_comment'));
        $formAddComment->setSuccessMessage(Session::getFlash('success-form-add_comment'));
        $formAddComment->setSuccessMessage(Session::getFlash('success-form-add_comment'));
        $this->data += ['form-add_comment' => $formAddComment];
        $this->data += ['form-add_comment-comment_id' => Session::getFlash('form-add_comment-comment_id')];

        $formEditComment = new FormHelper();
        $formEditComment->setInputsValues(Session::getFlash('form-edit_comment-values'));
        $formEditComment->setInputsErrors(Session::getFlash('form-edit_comment-errors'));
        $formEditComment->setErrorMessage(Session::getFlash('error-form-edit_comment'));
        $formEditComment->setSuccessMessage(Session::getFlash('success-form-edit_comment'));
        $this->data += ['form-edit_comment' => $formEditComment];
        $this->data += ['form-edit_comment-comment_id' => Session::getFlash('form-edit_comment-comment_id')];

        $formDeleteComment = new FormHelper();
        $formDeleteComment->setErrorMessage(Session::getFlash('error-form-delete_comment'));
        $formDeleteComment->setSuccessMessage(Session::getFlash('success-form-delete_comment'));
        $this->data += ['form-delete_comment' => $formDeleteComment];

        return $this->sendPage();
    }

    /**
     * @throws \Exception
     */
    protected function getBlueprint(ServerRequestInterface $request): ?array
    {
        $slug = $request->getAttribute('blueprint_slug');

        $blueprint = BlueprintService::getFromSlug($slug);
        if ($blueprint === null) {
            return null;
        }

        if ($this->userID === $blueprint['id_author']) {
            return $blueprint;
        }

        if ($blueprint['exposure'] === 'private') {
            return null;
        }

        return $blueprint;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function getBlueprintVersions(ServerRequestInterface $request, array $blueprint): ?array
    {
        $version = $request->getAttribute('version');

        $blueprint['versions'] = BlueprintService::getAllVersions($blueprint['id']);
        if ($blueprint['versions'] === null) {
            return null;
        }

        if ($version === 'last') {
            $blueprint['versions'][0]['current'] = true;
            $blueprint['current_version'] = $blueprint['versions'][0]['version'];

            $blueprint['page_url'] = Helper::getBlueprintLink($blueprint['slug']);
            $blueprint['render_url'] = Helper::getBlueprintRenderLink($blueprint['slug']);
        } else {
            $version = (int) $version;
            $hasFoundVersion = false;

            foreach ($blueprint['versions'] as $k => $v) {
                if ($v['version'] === $version) {
                    $blueprint['versions'][$k]['current'] = true;
                    $hasFoundVersion = true;
                    $blueprint['current_version'] = $version;

                    $blueprint['page_url'] = Helper::getBlueprintLink($blueprint['slug'], $blueprint['current_version']);
                    $blueprint['render_url'] = Helper::getBlueprintRenderLink($blueprint['slug'], $blueprint['current_version']);

                    break;
                }
            }

            if ($hasFoundVersion === false) {
                return null;
            }
        }

        return $blueprint;
    }

    // region form delete_blueprint

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Exception
     */
    protected function doProcessDeleteBlueprint(array $blueprint, string $pageURL): ResponseInterface
    {
        $anonymousID = (int) Application::getConfig()->get('ANONYMOUS_ID');

        if ($anonymousID !== $blueprint['id_author']) {
            Session::setFlash('error-form-delete_blueprint', 'Error, delete is invalid on this blueprint');
            Session::keepFlash(['error-form-delete_blueprint']);

            return $this->redirect($pageURL);
        }

        $foundBlueprint = false;
        $newAnonymousBlueprints = [];
        $anonymousBlueprints = Session::get('anonymous_blueprints') ?? [];
        foreach ($anonymousBlueprints as $anonymousBlueprintID) {
            if ($anonymousBlueprintID === $blueprint['id']) {
                $foundBlueprint = true;
                continue;
            }

            $newAnonymousBlueprints[] = $anonymousBlueprintID;
        }
        Session::set('anonymous_blueprints', $newAnonymousBlueprints);

        if (!$foundBlueprint) {
            Session::setFlash('error-form-delete_blueprint', 'Error, delete is invalid on this blueprint');
            Session::keepFlash(['error-form-delete_blueprint']);

            return $this->redirect($pageURL);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            BlueprintService::deleteBlueprint($blueprint['id']);
            $comments = CommentService::getAllCommentsWithBlueprintID($blueprint['id']);
            CommentService::deleteCommentsWithBlueprintID($blueprint['id']);

            if ($blueprint['exposure'] === 'public') {
                UserService::updatePublicAndPrivateBlueprintCount($blueprint['id_author'], -1);
                if ($comments !== null) {
                    UserService::updatePublicAndPrivateCommentCountWithComments($comments);
                }
            } else {
                UserService::updatePrivateBlueprintCount($blueprint['id_author'], -1);
                if ($comments !== null) {
                    UserService::updatePrivateCommentCountWithComments($comments);
                }
            }
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because requirements checkings has been done before
             * For covering we have to mock the database
             */
            Session::setFlash('error-form-delete_blueprint', 'Error, delete is impossible for the moment');
            Session::keepFlash(['error-form-delete_blueprint']);
            // @codeCoverageIgnoreEnd
        } finally {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->completeTransaction();
        }

        return $this->redirect('/');
    }

    // endregion

    // region form claim_blueprint

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Exception
     */
    protected function doProcessClaimBlueprint(array $blueprint): ResponseInterface
    {
        $anonymousID = (int) Application::getConfig()->get('ANONYMOUS_ID');

        if ($anonymousID !== $blueprint['id_author']) {
            Session::setFlash('error-form-claim_blueprint', 'Error, claim is invalid on this blueprint');
            Session::keepFlash(['error-form-claim_blueprint']);

            return $this->redirect($blueprint['page_url']);
        }

        $foundBlueprint = false;
        $newAnonymousBlueprints = [];
        $anonymousBlueprints = Session::get('anonymous_blueprints') ?? [];
        foreach ($anonymousBlueprints as $anonymousBlueprintID) {
            if ($anonymousBlueprintID === $blueprint['id']) {
                $foundBlueprint = true;
                continue;
            }

            $newAnonymousBlueprints[] = $anonymousBlueprintID;
        }
        Session::set('anonymous_blueprints', $newAnonymousBlueprints);

        if ($foundBlueprint) {
            try {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->startTransaction();

                BlueprintService::claimBlueprint($blueprint['id'], $this->userID);

                if ($blueprint['exposure'] === 'public') {
                    UserService::updatePublicAndPrivateBlueprintCount($this->userID, 1);
                    UserService::updatePublicAndPrivateBlueprintCount($blueprint['id_author'], -1);
                } else {
                    UserService::updatePrivateBlueprintCount($this->userID, 1);
                    UserService::updatePrivateBlueprintCount($blueprint['id_author'], -1);
                }

                Session::setFlash('success-form-claim_blueprint', 'This blueprint is now yours');
                Session::keepFlash(['success-form-claim_blueprint']);
                // @codeCoverageIgnoreStart
            } catch (\Exception $exception) {
                /*
                 * In end 2 end testing we can't arrive here because requirements checkings has been done before
                 * For covering we have to mock the database
                 */
                Session::setFlash('error-form-claim_blueprint', 'Error, claim is impossible for the moment');
                Session::keepFlash(['error-form-claim_blueprint']);
                // @codeCoverageIgnoreEnd
            } finally {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        } else {
            Session::setFlash('error-form-claim_blueprint', 'Error, claim is invalid on this blueprint');
            Session::keepFlash(['error-form-claim_blueprint']);
        }

        // remove session anonymous blueprints
        return $this->redirect($blueprint['page_url']);
    }

    // endregion

    // region form delete version blueprint

    /**
     * @throws \Exception
     */
    protected function treatFormDeleteVersion(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs['delete_version_blueprint']);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // version
        $values['version'] = (int) $params[$this->inputs['delete_version_blueprint']['version']];
        if ($values['version'] < 1) {
            $errors['version'] = 'Version is invalid';

            Session::setFlash('error-form-delete_version_blueprint', 'Error, version to delete is invalid');
            Session::setFlash('form-delete_version_blueprint-errors', $errors);
            Session::setFlash('form-delete_version_blueprint-values', $values);
            Session::keepFlash(['error-form-delete_version_blueprint', 'form-delete_version_blueprint-errors', 'form-delete_version_blueprint-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function doProcessDeleteVersionBlueprint(array $blueprint, ?array $params): ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($blueprint['page_url']);
        }

        if ($blueprint['id_author'] !== $this->userID) {
            Session::setFlash('error-form-delete_version_blueprint', 'Error, delete version is invalid on this blueprint');
            Session::keepFlash(['error-form-delete_version_blueprint']);

            return $this->redirect($blueprint['page_url']);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $error = BlueprintService::deleteVersion($blueprint['id'], $params['version']);
            if ($error !== null) {
                if ($error === -1) {
                    Session::setFlash('error-form-delete_version_blueprint', 'Error, blueprint must have one version left');
                    Session::keepFlash(['error-form-delete_version_blueprint']);
                } elseif ($error === -2) {
                    Session::setFlash('error-form-delete_version_blueprint', 'Error, version to delete is invalid');
                    Session::keepFlash(['error-form-delete_version_blueprint']);
                }

                return $this->redirect($blueprint['page_url']);
            }

            Session::setFlash('success-form-delete_version_blueprint', 'Version ' . $params['version'] . ' has been deleted');
            Session::keepFlash(['success-form-delete_version_blueprint']);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because requirements checkings has been done before
             * For covering we have to mock the database
             */
            Session::setFlash('error-form-delete_version_blueprint', 'Error, delete version is impossible for the moment');
            Session::keepFlash(['error-form-delete_version_blueprint']);
            // @codeCoverageIgnoreEnd
        } finally {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->completeTransaction();
        }

        return $this->redirect($blueprint['page_url']);
    }

    // endregion

    // region form add comment

    /**
     * @throws \Exception
     */
    protected function treatFormAddComment(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs['add_comment']);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // comment
        $values['comment'] = $params[$this->inputs['add_comment']['comment']];
        if ($values['comment'] === '') {
            $errors['comment'] = 'Comment is required';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-add_comment', 'Error, fields are invalid or required');
            Session::setFlash('form-add_comment-errors', $errors);
            Session::setFlash('form-add_comment-values', $values);
            Session::keepFlash(['error-form-add_comment', 'form-add_comment-errors', 'form-add_comment-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function doProcessAddComment(array $blueprint, ?array $params): ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($blueprint['page_url'] . '#comments');
        }

        $commentID = null;
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $commentID = CommentService::addComment($blueprint['id'], $this->userID, $params['comment']);
            if ($commentID === 0) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because requirements checkings has been done before
                 * For covering we have to mock the database
                 */
                Session::setFlash('error-form-add_comment', 'Error, could not create your comment');
                Session::keepFlash(['error-form-add_comment']);

                return $this->redirect($blueprint['page_url'] . '#comments');
                // @codeCoverageIgnoreEnd
            }

            BlueprintService::updateCommentCount($blueprint['id'], 1);

            if ($blueprint['exposure'] === 'public') {
                UserService::updatePublicAndPrivateCommentCount($this->userID, 1);
            } else {
                UserService::updatePrivateCommentCount($this->userID, 1);
            }

            Session::setFlash('success-form-add_comment', 'Your comment has been added');
            Session::setFlash('form-add_comment-comment_id', $commentID);
            Session::keepFlash(['success-form-add_comment', 'form-add_comment-comment_id']);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because requirements checkings has been done before
             * For covering we have to mock the database
             */
            Session::setFlash('error-form-add_comment', 'Error, could not add your comment for the moment');
            Session::keepFlash(['error-form-add_comment']);
            // @codeCoverageIgnoreEnd
        } finally {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->completeTransaction();
        }

        return $this->redirect($blueprint['page_url'] . '#comment-' . $commentID);
    }

    // endregion

    // region form edit_comment

    /**
     * @throws \Exception
     */
    protected function treatFormEditComment(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs['edit_comment']);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // commentID
        $values['id'] = (int) $params[$this->inputs['edit_comment']['id']];
        if ($values['id'] === 0) {
            $errors['id'] = 'Comment ID is invalid';
        }

        // comment
        $values['comment'] = $params[$this->inputs['edit_comment']['comment']];
        if ($values['comment'] === '') {
            $errors['comment'] = 'Comment is required';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-edit_comment', 'Error, fields are invalid or required');
            Session::setFlash('form-edit_comment-errors', $errors);
            Session::setFlash('form-edit_comment-values', $values);
            Session::setFlash('form-edit_comment-comment_id', ($values['id'] > 0) ? $values['id'] : null);
            Session::keepFlash(['error-form-edit_comment', 'form-edit_comment-errors', 'form-edit_comment-values', 'form-edit_comment-comment_id']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     */
    protected function doProcessEditComment(array $blueprint, ?array $params): ResponseInterface
    {
        $hash = '#comments';
        if ($params === null) {
            $values = Session::getFlash('form-edit_comment-values');
            if ($values['id'] > 0) {
                $hash = '#comment-' . $values['id'];
            }

            return $this->redirect($blueprint['page_url'] . $hash);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            if (!CommentService::isCommentBelongToAuthor($params['id'], $this->userID)) {
                Session::setFlash('error-form-edit_comment', 'Error, this comment does not belong to you');
                Session::setFlash('form-edit_comment-values', $params);
                Session::setFlash('form-edit_comment-comment_id', $params['id']);
                Session::keepFlash(['error-form-edit_comment', 'form-edit_comment-values', 'form-edit_comment-comment_id']);

                return $this->redirect($blueprint['page_url'] . '#comments');
            }

            CommentService::editComment($params['id'], $params['comment']);

            Session::setFlash('success-form-edit_comment', 'Your comment has been edited');
            Session::setFlash('form-edit_comment-values', $params);
            Session::setFlash('form-edit_comment-comment_id', $params['id']);
            Session::keepFlash(['success-form-edit_comment', 'form-edit_comment-values', 'form-edit_comment-comment_id']);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because requirements checkings has been done before
             * For covering we have to mock the database
             */
            Session::setFlash('error-form-delete_comment', 'Error, could not delete your comment for the moment');
            Session::setFlash('form-edit_comment-values', $params);
            Session::keepFlash(['error-form-delete_comment', 'form-edit_comment-values']);
            // @codeCoverageIgnoreEnd
        } finally {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->completeTransaction();
        }

        return $this->redirect($blueprint['page_url'] . '#comment-' . $params['id']);
    }

    // endregion

    // region form delete_comment

    /**
     * @throws \Exception
     */
    protected function treatFormDeleteComment(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs['delete_comment']);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // commentID
        $values['id'] = (int) $params[$this->inputs['delete_comment']['id']];
        if ($values['id'] === 0) {
            $errors['id'] = 'Comment ID is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-delete_comment', 'Error, fields are invalid or required');
            Session::setFlash('form-delete_comment-errors', $errors);
            Session::setFlash('form-delete_comment-values', $values);
            Session::keepFlash(['error-form-delete_comment', 'form-delete_comment-errors', 'form-delete_comment-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     */
    protected function doProcessDeleteComment(array $blueprint, ?array $params): ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($blueprint['page_url'] . '#comments');
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            if (!CommentService::isCommentBelongToAuthor($params['id'], $this->userID)) {
                Session::setFlash('error-form-delete_comment', 'Error, this comment does not belong to you');
                Session::keepFlash(['error-form-delete_comment']);

                return $this->redirect($blueprint['page_url'] . '#comments');
            }

            CommentService::deleteComment($params['id']);

            BlueprintService::updateCommentCount($blueprint['id'], -1);

            if ($blueprint['exposure'] === 'public') {
                UserService::updatePublicAndPrivateCommentCount($this->userID, -1);
            } else {
                UserService::updatePrivateCommentCount($this->userID, -1);
            }

            Session::setFlash('success-form-delete_comment', 'Your comment has been deleted');
            Session::keepFlash(['success-form-delete_comment']);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            /*
             * In end 2 end testing we can't arrive here because requirements checkings has been done before
             * For covering we have to mock the database
             */
            Session::setFlash('error-form-delete_comment', 'Error, could not delete your comment for the moment');
            Session::keepFlash(['error-form-delete_comment']);
            // @codeCoverageIgnoreEnd
        } finally {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->completeTransaction();
        }

        return $this->redirect($blueprint['page_url'] . '#comments');
    }

    // endregion
}
