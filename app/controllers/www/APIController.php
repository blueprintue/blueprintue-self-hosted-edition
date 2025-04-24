<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Http\Message\Stream;
use Rancoud\Security\Security;

class APIController implements MiddlewareInterface
{
    /**
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$userID, $error] = $this->findUserWithApiKey($request);
        if ($error !== null) {
            return $this->sendUnauthorizedError($error);
        }

        /** @noinspection NullPointerExceptionInspection */
        $currentRoute = Application::getRouter()->getCurrentRoute()->getCallback()->getCurrentRoute()->getName();
        if ($currentRoute === 'api_render') {
            $blueprint = $this->treatParamsRender($request);

            return $this->doProcessRender($blueprint);
        }

        if ($currentRoute === 'api_upload') {
            [$params, $error] = $this->treatParamsUpload($request);
            if ($error !== null) {
                $dataError = \json_encode(['error' => $error], \JSON_THROW_ON_ERROR);

                return (new Factory())->createResponse()->withBody(Stream::create($dataError))->withHeader('Content-type', 'application/json')->withStatus(400);
            }

            return $this->doProcessUpload($userID, $params);
        }

        // @codeCoverageIgnoreStart
        /*
         * it is not possible to reach this statement because all possibilities have been implemented.
         */
        return (new Factory())->createResponse()->withStatus(404);
        // @codeCoverageIgnoreEnd
    }

    // region API Key
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    protected function findUserWithApiKey(ServerRequestInterface $request): array
    {
        $apiKey = Helper::trim($request->getHeaderLine('X-Token'));
        if ($apiKey === '') {
            return [null, 'api_key_empty'];
        }

        // avoid bad encoding string
        try {
            Security::escHTML($apiKey);
        } catch (\Exception $e) {
            return [null, 'api_key_incorrect'];
        }

        $userID = UserService::findUserIDWithApiKey($apiKey);
        if ($userID === null) {
            return [null, 'api_key_incorrect'];
        }

        return [$userID, null];
    }

    /**
     * @throws \Exception
     */
    protected function sendUnauthorizedError(string $error): ResponseInterface
    {
        $contentType = 'application/json';
        $data = \json_encode(['error' => $error], \JSON_THROW_ON_ERROR);

        return (new Factory())->createResponse()->withBody(Stream::create($data))->withHeader('Content-type', $contentType)->withStatus(401);
    }
    // endregion

    // region Render
    protected function treatParamsRender(ServerRequestInterface $request): ?string
    {
        $rawParams = $request->getParsedBody();
        if (!isset($rawParams['blueprint'])) {
            return null;
        }

        // avoid bad encoding string
        try {
            Security::escHTML($rawParams['blueprint']);
        } catch (\Exception $e) {
            return null;
        }

        $blueprint = Helper::trim($rawParams['blueprint']);
        if (!BlueprintService::isValidBlueprint($blueprint)) {
            return null;
        }

        return $blueprint;
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function doProcessRender(?string $blueprint): ResponseInterface
    {
        if ($blueprint === null) {
            $dataError = \json_encode(['error' => 'blueprint_empty'], \JSON_THROW_ON_ERROR);

            return (new Factory())->createResponse()->withBody(Stream::create($dataError))->withHeader('Content-type', 'application/json')->withStatus(400);
        }

        $data = [
            'host'        => Helper::getHostname(),
            'title'       => Application::getConfig()->get('SITE_BASE_TITLE', ''),
            'description' => 'No description',
            'content'     => $blueprint,
        ];

        $html = $this->generateHTML($data);

        return (new Factory())->createResponse()->withBody(Stream::create($html))->withHeader('Content-type', 'text/html');
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     */
    protected function generateHTML(array $data): string
    {
        \ob_start();
        require Application::getFolder('VIEWS') . 'www/api/render.php';

        return \ob_get_clean();
    }
    // endregion

    // region Upload
    protected function treatParamsUpload(ServerRequestInterface $request): array
    {
        $rawParams = $request->getParsedBody();

        // avoid bad encoding string
        try {
            foreach ($rawParams as $rawParamValue) {
                Security::escHTML($rawParamValue);
            }
        } catch (\Exception $e) {
            return [null, 'invalid'];
        }

        $title = Helper::trim($rawParams['title'] ?? '');
        if ($title === '') {
            return [null, 'required_title'];
        }

        $blueprint = Helper::trim($rawParams['blueprint'] ?? '');
        if (!BlueprintService::isValidBlueprint($blueprint)) {
            return [null, 'invalid_blueprint'];
        }

        $exposure = Helper::trim($rawParams['exposure'] ?? 'public');
        if (!\in_array($exposure, ['public', 'unlisted', 'private'], true)) {
            return [null, 'invalid_exposure'];
        }

        $expiration = Helper::trim($rawParams['expiration'] ?? 'never');
        if (!\in_array($expiration, ['never', '3600', '86400', '604800'], true)) {
            return [null, 'invalid_expiration'];
        }

        $oldExpiration = ['never' => 'never', '3600' => '1h', '86400' => '1d', '604800' => '1w'];
        $expiration = $oldExpiration[$expiration];

        $version = Helper::trim($rawParams['version'] ?? Helper::getCurrentUEVersion());
        if (!\in_array($version, Helper::getAllUEVersion(), true)) {
            return [null, 'invalid_version'];
        }

        return [
            [
                'title'      => $title,
                'blueprint'  => $blueprint,
                'exposure'   => $exposure,
                'expiration' => $expiration,
                'ue_version' => $version,
            ],
            null
        ];
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function doProcessUpload(int $userID, array $params): ResponseInterface
    {
        $blueprintSlug = '';
        $forceRollback = false;
        $errorMessage = 'error_insert_blueprint';
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $params['id_author'] = $userID;
            [$blueprint, $errorCode] = BlueprintService::createFromAPI($params);
            if ($blueprint === null) {
                $errorMessage = 'error_insert_blueprint_' . $errorCode;

                throw new \Exception($errorMessage);
            }

            $errorMessage = 'error_insert_blueprint_#500';
            if ($params['exposure'] === 'public') {
                UserService::updatePublicAndPrivateBlueprintCount($params['id_author'], 1);
            } else {
                UserService::updatePrivateBlueprintCount($params['id_author'], 1);
            }

            $blueprintSlug = $blueprint['slug'];
        } catch (\Exception $exception) {
            $forceRollback = true;

            $dataError = \json_encode(['error' => $errorMessage], \JSON_THROW_ON_ERROR);

            return (new Factory())->createResponse()->withBody(Stream::create($dataError))->withHeader('Content-type', 'application/json')->withStatus(400);
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        $data = \json_encode(['key' => $blueprintSlug], \JSON_THROW_ON_ERROR);

        return (new Factory())->createResponse()->withBody(Stream::create($data))->withHeader('Content-type', 'application/json');
    }
    // endregion
}
