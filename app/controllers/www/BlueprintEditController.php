<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\TagService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

class BlueprintEditController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected ?int $userID = null;

    protected ?int $blueprintID = null;

    protected string $exposure = 'public';

    protected ?string $expiration = null;

    protected string $blueprintEditURL = '/';

    protected array $formCsrfKeys = [
        'form-delete_thumbnail-hidden-csrf'  => 'delete_thumbnail',
        'form-edit_informations-hidden-csrf' => 'edit_informations',
        'form-edit_properties-hidden-csrf'   => 'edit_properties',
        'form-add_version-hidden-csrf'       => 'add_version',
        'form-delete_blueprint-hidden-csrf'  => 'delete_blueprint',
    ];

    protected array $inputs = [
        'delete_thumbnail' => [
            'CSRF'    => 'form-delete_thumbnail-hidden-csrf'
        ],
        'edit_informations' => [
            'title'       => 'form-edit_informations-input-title',
            'description' => 'form-edit_informations-textarea-description',
            'tags'        => 'form-edit_informations-textarea-tags',
            'video'       => 'form-edit_informations-input-video',
            'CSRF'        => 'form-edit_informations-hidden-csrf'
        ],
        'edit_properties' => [
            'exposure'   => 'form-edit_properties-select-exposure',
            'expiration' => 'form-edit_properties-select-expiration',
            'ue_version' => 'form-edit_properties-select-ue_version',
            'comment'    => 'form-edit_properties-select-comment',
            'CSRF'       => 'form-edit_properties-hidden-csrf'
        ],
        'add_version' => [
            'blueprint' => 'form-add_version-textarea-blueprint',
            'reason'    => 'form-add_version-textarea-reason',
            'CSRF'      => 'form-add_version-hidden-csrf',
        ],
        'delete_blueprint' => [
            'ownership' => 'form-delete_blueprint-select-ownership',
            'CSRF'      => 'form-delete_blueprint-hidden-csrf'
        ]
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'blueprint_edit';
        $this->currentPageForNavBar = 'blueprint_edit';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('blueprint-edit', ['blueprint_slug' => $data['slug']]) ?? '/';

        $this->title = 'Edit blueprint ' . $data['title'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $this->description = 'Edit blueprint ' . $data['title'];
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // user access
        $this->userID = Session::get('userID');
        if ($this->userID === null || $this->userID === (int) Application::getConfig()->get('ANONYMOUS_ID')) {
            return $this->redirect(Helper::getBlueprintLink($request->getAttribute('blueprint_slug')));
        }

        // blueprint access
        $blueprint = $this->getBlueprint($request);
        if ($blueprint === null) {
            return $this->redirect(Helper::getBlueprintLink($request->getAttribute('blueprint_slug')));
        }

        $this->blueprintID = $blueprint['id'];
        $this->exposure = $blueprint['exposure'];
        $this->expiration = $blueprint['expiration'];
        $this->blueprintEditURL = Application::getRouter()->generateUrl('blueprint-edit', ['blueprint_slug' => $blueprint['slug']]) ?? '/';

        // form
        $response = $this->treatFormSent($request);
        if ($response !== null) {
            return $response;
        }

        $this->computeDataForRender($blueprint);

        return $this->sendPage();
    }

    /** @throws \Exception */
    protected function getBlueprint(ServerRequestInterface $request): ?array
    {
        $slug = $request->getAttribute('blueprint_slug');

        $blueprint = BlueprintService::getFromSlug($slug);
        if ($blueprint === null) {
            return null;
        }

        if ($this->userID !== $blueprint['id_author']) {
            return null;
        }

        return $blueprint;
    }

    /** @throws \Exception */
    protected function treatFormSent(ServerRequestInterface $request): ?ResponseInterface
    {
        $formKey = $this->findFormSent($request);
        if ($formKey !== null) {
            switch ($formKey) {
                case 'delete_thumbnail':
                    return $this->doProcessDeleteThumbnail();
                case 'edit_informations':
                    $cleanedParams = $this->treatFormEditInformations($request, $this->inputs['edit_informations']);

                    return $this->doProcessEditInformations($cleanedParams);
                case 'edit_properties':
                    $cleanedParams = $this->treatFormEditProperties($request, $this->inputs['edit_properties']);

                    return $this->doProcessEditProperties($cleanedParams);
                case 'add_version':
                    $cleanedParams = $this->treatFormAddVersion($request, $this->inputs['add_version']);

                    return $this->doProcessAddVersion($cleanedParams);
                case 'delete_blueprint':
                    $cleanedParams = $this->treatFormDeleteBlueprint($request, $this->inputs['delete_blueprint']);

                    return $this->doProcessDeleteBlueprint($cleanedParams);
            }
        }

        return null;
    }

    /** @throws \Exception */
    protected function findFormSent(ServerRequestInterface $request): ?string
    {
        if ($request->getMethod() !== 'POST') {
            return null;
        }

        $rawParams = $request->getParsedBody();

        $formKeyFound = null;
        foreach ($this->formCsrfKeys as $csrfFieldName => $formCsrfKey) {
            if (isset($rawParams[$csrfFieldName])) {
                $formKeyFound = $formCsrfKey;

                break;
            }
        }

        if ($formKeyFound === null) {
            return null;
        }

        $csrf = Session::get('csrf');
        if (empty($csrf) || !isset($rawParams[$this->inputs[$formKeyFound]['CSRF']]) || $csrf !== $rawParams[$this->inputs[$formKeyFound]['CSRF']]) {
            return null;
        }

        foreach ($this->inputs[$formKeyFound] as $key => $input) {
            if ($key === 'CSRF') {
                continue;
            }

            if (!isset($rawParams[$input])) {
                $this->setAndKeepInfos('error-form-' . $formKeyFound, 'Error, missing fields');

                return null;
            }

            // avoid bad encoding string
            try {
                Security::escHTML($rawParams[$input]);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $formKeyFound;
    }

    protected function cleanRawParamsInRequest(ServerRequestInterface $request, array $inputs): array
    {
        $params = [];
        $htmlNames = \array_values($inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        return $params;
    }

    // region Delete Thumbnail
    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    protected function doProcessDeleteThumbnail(): ResponseInterface
    {
        if (BlueprintService::updateThumbnail($this->blueprintID, null)) {
            Session::setFlash('success-form-delete_thumbnail', 'Blueprint thumbnail is now deleted');
            Session::keepFlash(['success-form-delete_thumbnail']);
        } else {
            // @codeCoverageIgnoreStart
            /*
             * I don't know how to produce this error
             */
            Session::setFlash('error-form-delete_thumbnail', 'Error, could not delete blueprint thumbnail');
            Session::keepFlash(['error-form-delete_thumbnail']);
            // @codeCoverageIgnoreEnd
        }

        return $this->redirect($this->blueprintEditURL);
    }
    // endregion

    // region Edit Informations
    /** @throws \Exception */
    protected function treatFormEditInformations(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // title
        $values['title'] = $params[$inputs['title']];
        if ($values['title'] === '') {
            $errorsForMessage[] = 'title';
            $errors['title'] = 'Title is required';
        }

        // description
        $values['description'] = $params[$inputs['description']];
        if ($values['description'] === '') {
            $values['description'] = null;
        }

        // tags
        $values['tags'] = $params[$inputs['tags']];
        if ($values['tags'] === '') {
            $values['tags'] = null;
        }

        // video
        $values['video'] = $params[$inputs['video']];
        [$video, $provider] = BlueprintService::findVideoProvider($values['video']);
        if ($video !== null) {
            $values['video'] = $video;
            $values['video_provider'] = $provider;
        } elseif ($values['video'] === '') {
            $values['video'] = null;
            $values['video_provider'] = null;
        } else {
            $errorsForMessage[] = 'video';
            $errors['video'] = 'Video is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-edit_informations', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-edit_informations-errors', $errors);
            Session::setFlash('form-edit_informations-values', $values);
            Session::keepFlash(['error-form-edit_informations', 'form-edit_informations-errors', 'form-edit_informations-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    protected function doProcessEditInformations(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->blueprintEditURL);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $informations = [];

            // title
            $informations['title'] = $params['title'];

            // description
            $informations['description'] = $params['description'];

            // tags
            $informations['tags'] = null;
            if ($params['tags'] !== null) {
                $informations['tags'] = TagService::createAndFindTagsWithTextareaTags($params['tags']);
            }

            // video
            $informations['video'] = $params['video'];
            $informations['video_provider'] = $params['video_provider'];

            BlueprintService::updateInformations($this->blueprintID, $informations);

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->commitTransaction();
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->rollbackTransaction();

            unset($params['video_provider']);

            Session::setFlash('error-form-edit_informations', 'Error, could not update blueprint informations');
            Session::setFlash('form-edit_informations-values', $params);
            Session::keepFlash(['error-form-edit_informations', 'form-edit_informations-values']);

            return $this->redirect($this->blueprintEditURL);
            // @codeCoverageIgnoreEnd
        }

        Session::setFlash('success-form-edit_informations', 'Informations has been updated');
        Session::keepFlash(['success-form-edit_informations']);

        return $this->redirect($this->blueprintEditURL);
    }
    // endregion

    // region Edit Properties
    /** @throws \Exception */
    protected function treatFormEditProperties(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // exposure
        $values['exposure'] = $params[$inputs['exposure']];
        if (\in_array($values['exposure'], ['public', 'unlisted', 'private'], true) === false) {
            $errorsForMessage[] = 'exposure';
            $errors['exposure'] = 'Exposure is invalid';
        }

        // expiration
        $values['expiration'] = $params[$inputs['expiration']];
        if (\in_array($values['expiration'], ['keep', '1h', '1d', '1w', 'remove'], true) === false) {
            $errorsForMessage[] = 'expiration';
            $errors['expiration'] = 'Expiration is invalid';
        }

        if ($this->expiration === null && $values['expiration'] === 'remove') {
            $errorsForMessage[] = 'expiration';
            $errors['expiration'] = 'Expiration is invalid';
        }

        // ue_version
        $values['ue_version'] = $params[$inputs['ue_version']];
        if (\in_array($values['ue_version'], Helper::getAllUEVersion(), true) === false) {
            $errorsForMessage[] = 'UE version';
            $errors['ue_version'] = 'UE version is invalid';
        }

        // comment
        $values['comment'] = $params[$inputs['comment']];
        if (\in_array($values['comment'], ['open', 'close', 'hide'], true) === false) {
            $errorsForMessage[] = 'comment section';
            $errors['comment'] = 'Comment section is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-edit_properties', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-edit_properties-errors', $errors);
            Session::setFlash('form-edit_properties-values', $values);
            Session::keepFlash(['error-form-edit_properties', 'form-edit_properties-errors', 'form-edit_properties-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    protected function doProcessEditProperties(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->blueprintEditURL);
        }

        $properties = [];

        // exposure
        $properties['exposure'] = $params['exposure'];

        // expiration
        if ($params['expiration'] === 'remove') {
            $properties['expiration'] = null;
        } elseif ($params['expiration'] === 'keep') {
            $properties['expiration'] = $this->expiration;
        } else {
            $convert = [
                '1h' => 'hour',
                '1d' => 'day',
                '1w' => 'week'
            ];

            $startDate = $this->expiration ?? 'now';
            $startDateAsDateTime = (new \DateTimeImmutable($startDate, new \DateTimeZone('UTC')));
            $expirationDateTime = $startDateAsDateTime->modify('+1 ' . $convert[$params['expiration']]);
            $properties['expiration'] = $expirationDateTime->format('Y-m-d H:i:s');
        }

        // ue_version
        $properties['ue_version'] = $params['ue_version'];

        // comment
        $properties['comments_hidden'] = 0;
        $properties['comments_closed'] = 0;
        if ($params['comment'] === 'close') {
            $properties['comments_hidden'] = 0;
            $properties['comments_closed'] = 1;
        } elseif ($params['comment'] === 'hide') {
            $properties['comments_hidden'] = 1;
            $properties['comments_closed'] = 1;
        }

        BlueprintService::updateProperties($this->blueprintID, $properties);

        Session::setFlash('success-form-edit_properties', 'Properties has been updated');
        Session::keepFlash(['success-form-edit_properties']);

        return $this->redirect($this->blueprintEditURL);
    }
    // endregion

    // region Add Version
    /** @throws \Exception */
    protected function treatFormAddVersion(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // blueprint
        $values['blueprint'] = $params[$inputs['blueprint']];
        if ($values['blueprint'] === '') {
            $errorsForMessage[] = 'blueprint';
            $errors['blueprint'] = 'Blueprint is required';
        } elseif (!BlueprintService::isValidBlueprint($values['blueprint'])) {
            $errorsForMessage[] = 'blueprint';
            $errors['blueprint'] = 'Blueprint is invalid';
        }

        // reason
        $values['reason'] = $params[$inputs['reason']];
        if ($values['reason'] === '') {
            $errorsForMessage[] = 'reason';
            $errors['reason'] = 'Reason is required';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-add_version', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-add_version-errors', $errors);
            Session::setFlash('form-add_version-values', $values);
            Session::keepFlash(['error-form-add_version', 'form-add_version-errors', 'form-add_version-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    protected function doProcessAddVersion(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->blueprintEditURL);
        }

        $errorCode = BlueprintService::addVersion($this->blueprintID, $params['blueprint'], $params['reason']);
        if ($errorCode !== null) {
            Session::setFlash('error-form-add_version', 'Error, could not add version blueprint (' . $errorCode . ')');
            Session::setFlash('form-add_version-values', $params);
            Session::keepFlash(['error-form-add_version', 'form-add_version-values']);
        } else {
            Session::setFlash('success-form-add_version', 'The new version has been published');
            Session::keepFlash(['success-form-add_version']);
        }

        return $this->redirect($this->blueprintEditURL);
    }
    // endregion

    // region Delete Blueprint
    /** @throws \Exception */
    protected function treatFormDeleteBlueprint(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // ownership
        $values['ownership'] = $params[$inputs['ownership']];
        if (!\in_array($values['ownership'], ['give', 'delete'], true)) {
            $errorsForMessage[] = 'ownership';
            $errors['ownership'] = 'Ownership is invalid';
        }

        if ($this->exposure === 'private' && $values['ownership'] === 'give') {
            $errorsForMessage[] = 'ownership';
            $errors['ownership'] = 'Ownership is invalid, you can\'t give blueprint when having private exposure';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-delete_blueprint', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-delete_blueprint-errors', $errors);
            Session::setFlash('form-delete_blueprint-values', $values);
            Session::keepFlash(['error-form-delete_blueprint', 'form-delete_blueprint-errors', 'form-delete_blueprint-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    protected function doProcessDeleteBlueprint(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->blueprintEditURL);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $anonymousID = (int) Application::getConfig()->get('ANONYMOUS_ID');

            if ($params['ownership'] === 'give' && $anonymousID > 0) {
                BlueprintService::changeBlueprintAuthor($this->blueprintID, $anonymousID);
                UserService::updatePublicAndPrivateBlueprintCount($anonymousID, 1);
            } else {
                BlueprintService::changeBlueprintAuthor($this->blueprintID, null);
                BlueprintService::softDeleteBlueprint($this->blueprintID);
            }

            if ($this->exposure === 'private') {
                UserService::updatePrivateBlueprintCount($this->userID, -1);
            } else {
                UserService::updatePublicAndPrivateBlueprintCount($this->userID, -1);
            }

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->commitTransaction();
            // @codeCoverageIgnoreStart
        } catch (\Exception $e) {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->rollbackTransaction();

            Session::setFlash('error-form-delete_profile', 'Error, could not delete your blueprint');
            Session::keepFlash(['error-form-delete_profile']);

            return $this->redirect($this->blueprintEditURL);
            // @codeCoverageIgnoreEnd
        }

        return (new Factory())->createResponse(301)->withHeader('Location', '/');
    }
    // endregion

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function computeDataForRender(array $blueprint): void
    {
        $this->computeEditBlueprint($blueprint);

        $csrf = Session::get('csrf');

        $this->data += [$this->inputs['delete_thumbnail']['CSRF'] => $csrf];
        $this->data += [$this->inputs['edit_informations']['CSRF'] => $csrf];
        $this->data += [$this->inputs['edit_properties']['CSRF'] => $csrf];
        $this->data += [$this->inputs['add_version']['CSRF'] => $csrf];
        $this->data += [$this->inputs['delete_blueprint']['CSRF'] => $csrf];
        $this->data += ['data-uploader-upload-params-csrf' => $csrf];

        $formDeleteThumbnail = new FormHelper();
        $formDeleteThumbnail->setErrorMessage(Session::getFlash('error-form-delete_thumbnail'));
        $formDeleteThumbnail->setSuccessMessage(Session::getFlash('success-form-delete_thumbnail'));
        $this->data += ['form-delete_thumbnail' => $formDeleteThumbnail];

        $formEditInformations = new FormHelper();
        $formEditInformations->setInputsValues(Session::getFlash('form-edit_informations-values'));
        $formEditInformations->setInputsErrors(Session::getFlash('form-edit_informations-errors'));
        $formEditInformations->setInputValueIfEmpty('title', $this->data['blueprint']['title']);
        $formEditInformations->setInputValueIfEmpty('description', $this->data['blueprint']['description']);
        $formEditInformations->setInputValueIfEmpty('video', $this->data['blueprint']['video']);
        $formEditInformations->setErrorMessage(Session::getFlash('error-form-edit_informations'));
        $formEditInformations->setSuccessMessage(Session::getFlash('success-form-edit_informations'));
        $this->data += ['form-edit_informations' => $formEditInformations];

        $formEditProperties = new FormHelper();
        $formEditProperties->setInputsValues(Session::getFlash('form-edit_properties-values'));
        $formEditProperties->setInputsErrors(Session::getFlash('form-edit_properties-errors'));
        $formEditProperties->setInputValueIfEmpty('exposure', $this->data['blueprint']['exposure']);
        $formEditProperties->setInputValueIfEmpty('ue_version', $this->data['blueprint']['ue_version']);
        $formEditProperties->setInputValueIfEmpty('comment', $this->data['comment']);
        $formEditProperties->setErrorMessage(Session::getFlash('error-form-edit_properties'));
        $formEditProperties->setSuccessMessage(Session::getFlash('success-form-edit_properties'));
        $this->data += ['form-edit_properties' => $formEditProperties];

        $formAddVersion = new FormHelper();
        $formAddVersion->setInputsValues(Session::getFlash('form-add_version-values'));
        $formAddVersion->setInputsErrors(Session::getFlash('form-add_version-errors'));
        $formAddVersion->setErrorMessage(Session::getFlash('error-form-add_version'));
        $formAddVersion->setSuccessMessage(Session::getFlash('success-form-add_version'));
        $this->data += ['form-add_version' => $formAddVersion];

        $formDeleteBlueprint = new FormHelper();
        $formDeleteBlueprint->setInputsValues(Session::getFlash('form-delete_blueprint-values'));
        $formDeleteBlueprint->setInputsErrors(Session::getFlash('form-delete_blueprint-errors'));
        $formDeleteBlueprint->setErrorMessage(Session::getFlash('error-form-delete_blueprint'));
        $this->data += ['form-delete_blueprint' => $formDeleteBlueprint];

        $this->data += ['has_not_anonymous_user' => ((int) Application::getConfig()->get('ANONYMOUS_ID')) === 0];

        $this->setTemplateProperties([
            'slug'  => $this->data['blueprint']['slug'],
            'title' => $this->data['blueprint']['title']
        ]);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function computeEditBlueprint(array $blueprint): void
    {
        $this->data += ['blueprint_url' => Helper::getBlueprintLink($blueprint['slug'])];

        $tags = TagService::getTagsWithListIDs($blueprint['tags']);
        $tagsTextarea = '';
        if (!empty($tags)) {
            $tagsName = [];
            foreach ($tags as $tag) {
                $tagsName[] = $tag['name'];
            }
            $tagsTextarea = \implode("\n", $tagsName);
        }

        $blueprintForPage = [
            'id'                => $blueprint['id'],
            'slug'              => $blueprint['slug'],
            'thumbnail_url'     => Helper::getThumbnailUrl($blueprint['thumbnail']),
            'title'             => $blueprint['title'],
            'description'       => $blueprint['description'],
            'tags'              => $tags,
            'tags_textarea'     => $tagsTextarea,
            'video'             => $blueprint['video'],
            'exposure'          => $blueprint['exposure'],
            'expiration'        => $blueprint['expiration'],
            'ue_version'        => $blueprint['ue_version'],
            'versions'          => BlueprintService::getAllVersions($blueprint['id']),
            'current_version'   => $blueprint['current_version'],
        ];
        $this->data += ['blueprint' => $blueprintForPage];

        // comment
        $comment = 'open';
        if ($blueprint['comments_hidden'] === 1) {
            $comment = 'hide';
        } elseif ($blueprint['comments_closed'] === 1) {
            $comment = 'close';
        }
        $this->data += ['comment' => $comment];
    }
}
