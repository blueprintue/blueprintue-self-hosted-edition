<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\TemplateTrait;
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
use Rancoud\Session\Session;

class RenderController implements MiddlewareInterface
{
    use TemplateTrait;

    protected ?int $userID = null;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->isToHideFromGoogle = true;

        $this->url = Helper::getHostname() . $data['url'];

        $this->title = $data['title'] . ' posted by ' . $data['author'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $description = Helper::getFitSentence($data['description'] ?? '', 255);
        $this->description = ($description !== '') ? $description : 'No description provided';
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
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

        $users = UserService::getInfosFromIdAuthorIndex([$blueprint]);

        $this->setTemplateProperties([
            'url'         => $blueprint['render_url'],
            'title'       => $blueprint['title'],
            'author'      => $users[$blueprint['id_author']]['username'],
            'description' => $blueprint['description']
        ]);

        $content = BlueprintService::getBlueprintContent($blueprint['file_id'], $blueprint['current_version']);

        $data = [
            'host'    => Helper::getHostname(),
            'content' => $content,
        ];
        $html = $this->generateHTML($data);

        return (new Factory())->createResponse()->withBody(Stream::create($html));
    }

    /** @throws \Exception */
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

            $blueprint['render_url'] = Helper::getBlueprintRenderLink($blueprint['slug']);
        } else {
            $version = (int) $version;
            $hasFoundVersion = false;

            foreach ($blueprint['versions'] as $k => $v) {
                if ($v['version'] === $version) {
                    $blueprint['versions'][$k]['current'] = true;
                    $hasFoundVersion = true;
                    $blueprint['current_version'] = $version;

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

    /** @throws \Rancoud\Application\ApplicationException */
    protected function generateHTML(array $data): string
    {
        \ob_start();
        require Application::getFolder('VIEWS') . 'www/pages/render.php';

        return \ob_get_clean();
    }
}
