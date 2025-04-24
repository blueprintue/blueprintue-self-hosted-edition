<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Session\Session;

class HomeController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected array $inputs = [
        'CSRF'       => 'form-add_blueprint-hidden-csrf',
        'title'      => 'form-add_blueprint-input-title',
        'exposure'   => 'form-add_blueprint-select-exposure',
        'expiration' => 'form-add_blueprint-select-expiration',
        'ue_version' => 'form-add_blueprint-select-ue_version',
        'blueprint'  => 'form-add_blueprint-textarea-blueprint'
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     *
     * @noinspection PhpUnusedParameterInspection
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'home';
        $this->currentPageForNavBar = 'home';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('home');

        $this->title = (string) Application::getConfig()->get('SITE_BASE_TITLE', '');
        $this->description = (string) Application::getConfig()->get('SITE_DESCRIPTION', '');
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->hasSentForm($request, 'POST', $this->inputs, 'error-form-add_blueprint')) {
            $cleanedParams = $this->treatFormCreateBlueprint($request);

            return $this->doProcessCreateBlueprint($cleanedParams);
        }

        $this->setTemplateProperties();

        $this->data += ['blueprints' => $this->getLastFiveBlueprints()];
        $this->data += [$this->inputs['CSRF'] => Session::get('csrf')];

        $formAddBlueprint = new FormHelper();
        $formAddBlueprint->setInputsValues(Session::getFlash('form-add_blueprint-values'));
        $formAddBlueprint->setInputsErrors(Session::getFlash('form-add_blueprint-errors'));
        $formAddBlueprint->setErrorMessage(Session::getFlash('error-form-add_blueprint'));
        $formAddBlueprint->setSuccessMessage(Session::getFlash('success-form-add_blueprint'));
        $this->data += ['form-add_blueprint' => $formAddBlueprint];

        return $this->sendPage();
    }

    /** @throws \Exception */
    protected function treatFormCreateBlueprint(ServerRequestInterface $request): ?array
    {
        $params = [];
        $htmlNames = \array_values($this->inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        $errors = [];
        $values = [];

        // title
        $values['title'] = $params[$this->inputs['title']];
        if ($values['title'] === '') {
            $errors['title'] = 'Title is required';
        }

        // exposure
        $values['exposure'] = $params[$this->inputs['exposure']];
        if ($values['exposure'] === '') {
            $errors['exposure'] = 'Exposure is required';
        } elseif (!\in_array($values['exposure'], ['public', 'unlisted', 'private'], true)) {
            $errors['exposure'] = 'Exposure is invalid';
        } elseif ($values['exposure'] === 'private' && !Session::has('userID')) {
            $errors['exposure'] = 'Private is for member only';
        }

        // expiration
        $values['expiration'] = $params[$this->inputs['expiration']];
        if ($values['expiration'] === '') {
            $errors['expiration'] = 'Expiration is required';
        } elseif (!\in_array($values['expiration'], ['never', '1h', '1d', '1w'], true)) {
            $errors['expiration'] = 'Expiration is invalid';
        }

        // ue_version
        $values['ue_version'] = $params[$this->inputs['ue_version']];
        if ($values['ue_version'] === '') {
            $errors['ue_version'] = 'UE version is required';
        } elseif (!\in_array($values['ue_version'], Helper::getAllUEVersion(), true)) {
            $errors['ue_version'] = 'UE version is invalid';
        }

        // blueprint
        $values['blueprint'] = $params[$this->inputs['blueprint']];
        if ($values['blueprint'] === '') {
            $errors['blueprint'] = 'Blueprint is required';
        } elseif (!BlueprintService::isValidBlueprint($params[$this->inputs['blueprint']])) {
            $errors['blueprint'] = 'Blueprint is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-add_blueprint', 'Error, fields are invalid or required');
            Session::setFlash('form-add_blueprint-errors', $errors);
            Session::setFlash('form-add_blueprint-values', $values);
            Session::keepFlash(['error-form-add_blueprint', 'form-add_blueprint-errors', 'form-add_blueprint-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function doProcessCreateBlueprint(?array $params): ResponseInterface
    {
        if ($params === null) {
            return $this->redirect(Application::getRouter()->generateUrl('home'));
        }

        $blueprintID = 0;
        $blueprintSlug = '';
        $forceRollback = false;
        $errorMessage = 'Error';
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $params['id_author'] = Session::get('userID') ?? (int) Application::getConfig()->get('ANONYMOUS_ID');
            if ($params['id_author'] === 0) {
                $errorMessage = 'Error, could not create blueprint, anonymous user not supported (#50)';

                throw new \Exception($errorMessage);
            }

            [$blueprint, $errorCode] = BlueprintService::createFromHome($params);
            if ($blueprint === null) {
                $errorMessage = 'Error, could not create blueprint (' . $errorCode . ')';

                throw new \Exception($errorMessage);
            }

            $errorMessage = 'Error, could not create blueprint (#500)';
            if ($params['exposure'] === 'public') {
                UserService::updatePublicAndPrivateBlueprintCount($params['id_author'], 1);
            } else {
                UserService::updatePrivateBlueprintCount($params['id_author'], 1);
            }

            $blueprintID = $blueprint['id'];
            $blueprintSlug = $blueprint['slug'];
        } catch (\Exception $exception) {
            $forceRollback = true;

            Session::setFlash('error-form-add_blueprint', $errorMessage);
            Session::setFlash('form-add_blueprint-values', $params);
            Session::keepFlash(['error-form-add_blueprint', 'form-add_blueprint-values']);

            return $this->redirect(Application::getRouter()->generateUrl('home'));
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        if (!Session::has('userID')) {
            $anonymousBlueprints = Session::get('anonymous_blueprints') ?? [];
            $anonymousBlueprints[] = $blueprintID;
            Session::set('anonymous_blueprints', $anonymousBlueprints);
        }

        return $this->redirect(Helper::getBlueprintLink($blueprintSlug));
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function getLastFiveBlueprints(): ?array
    {
        $lastFiveBlueprints = BlueprintService::getLastFive();
        if ($lastFiveBlueprints === null) {
            return null;
        }

        $users = UserService::getInfosFromIdAuthorIndex($lastFiveBlueprints);

        foreach ($lastFiveBlueprints as $key => $blueprint) {
            $lastFiveBlueprints[$key]['author'] = Helper::formatUser($users[$blueprint['id_author']]);
        }

        return $lastFiveBlueprints;
    }
}
