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
use Rancoud\Session\Session;

class BlueprintDiffController implements MiddlewareInterface
{
    use TemplateTrait;

    protected ?int $userID = null;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'blueprint_diff';
        $this->currentPageForNavBar = 'blueprint';

        $this->url = Helper::getHostname() . $data['url'];

        $this->title = 'Diff between version ' . $data['previous_version'] . ' and ' . $data['current_version'] . ' for ' . $data['title'] . ' posted by ' . $data['author'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $description = Helper::getFitSentence($data['description'] ?? '', 255);
        $this->description = ($description !== '') ? $description : 'No description provided';
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
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

        // authorizations
        $this->isToHideFromGoogle = ($blueprint['exposure'] !== 'public');

        $users = UserService::getInfosFromIdAuthorIndex([$blueprint]);

        // template properties
        $this->setTemplateProperties([
            'url'              => Helper::getBlueprintDiffLink($blueprint['slug'], $blueprint['previous_version'], $blueprint['current_version']),
            'title'            => $blueprint['title'],
            'author'           => $users[$blueprint['id_author']]['username'],
            'description'      => $blueprint['description'],
            'previous_version' => $blueprint['previous_version'],
            'current_version'  => $blueprint['current_version'],
        ]);

        // data for page
        $blueprintData = [
            'versions'          => Helper::organizeVersionHistoryForDisplay($blueprint['slug'], $blueprint['versions']),
            'previous_content'  => BlueprintService::getBlueprintContent($blueprint['file_id'], $blueprint['previous_version']),
            'current_content'   => BlueprintService::getBlueprintContent($blueprint['file_id'], $blueprint['current_version']),
            'previous_version'  => $blueprint['previous_version'],
            'current_version'   => $blueprint['current_version'],
        ];
        $this->data += ['blueprint_back_url' => $blueprint['page_url']];
        $this->data += ['blueprint' => $blueprintData];

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
        $previousVersion = $request->getAttribute('previous_version');
        $currentVersion = $request->getAttribute('current_version');

        $blueprint['versions'] = BlueprintService::getAllVersions($blueprint['id']);
        if ($blueprint['versions'] === null) {
            return null;
        }

        $previousVersion = (int) $previousVersion;
        $currentVersion = (int) $currentVersion;
        $hasFoundLeftVersion = false;
        $hasFoundRightVersion = false;

        foreach ($blueprint['versions'] as $v) {
            if ($v['version'] === $previousVersion) {
                $hasFoundLeftVersion = true;
            }

            if ($v['version'] === $currentVersion) {
                $hasFoundRightVersion = true;
            }
        }

        if ($hasFoundLeftVersion === false || $hasFoundRightVersion === false) {
            return null;
        }

        $blueprint['previous_version'] = $previousVersion;
        $blueprint['current_version'] = $currentVersion;

        $blueprint['page_url'] = Helper::getBlueprintLink($blueprint['slug']);

        return $blueprint;
    }
}
