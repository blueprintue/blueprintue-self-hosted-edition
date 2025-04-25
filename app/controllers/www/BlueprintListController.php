<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\TemplateTrait;
use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\TagService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Pagination\Pagination;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

class BlueprintListController implements MiddlewareInterface
{
    use TemplateTrait;

    protected ?string $routeName = null;

    protected ?string $pageType = null;

    protected array $params = [
        'count_per_page'   => 20,
        'page'             => 1,
        'query'            => '',
        'type'             => '',
        'type_slug'        => '',
        'ue_version'       => '',
        'meta_title'       => '',
        'meta_description' => '',
        'tag'              => null,
        'tag_slug'         => '',
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'blueprints_list';
        $this->currentPageForNavBar = 'blueprints_list';

        $this->url = Helper::getHostname() . \str_replace('{{PAGE}}', (string) $data['page'], $data['url']);

        $this->title = $data['title'] . ' | Page ' . $data['page'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $this->description = $data['description'];
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Pagination\PaginationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $hasFound = $this->findPageType($request);
        if (!$hasFound) {
            return $this->redirect('/');
        }

        $this->setTemplateProperties([
            'page'        => $this->params['page'],
            'url'         => $this->configurationPagination['url'],
            'title'       => $this->params['meta_title'],
            'description' => $this->params['meta_description'],
        ]);

        $results = BlueprintService::search($this->pageType, Session::get('userID'), $this->params);
        if ($results['rows'] !== null) {
            $users = UserService::getInfosFromIdAuthorIndex($results['rows']);
            foreach ($results['rows'] as $key => $row) {
                $results['rows'][$key]['thumbnail_url'] = Helper::getThumbnailUrl($row['thumbnail']);
                $results['rows'][$key]['url'] = Helper::getBlueprintLink($row['slug']);
                $results['rows'][$key]['author'] = Helper::formatUser($users[$row['id_author']]);
                $results['rows'][$key]['since'] = Helper::getSince($row['published_at']);
            }
        }

        // search input
        $this->data += ['form-search-input-query' => $this->params['query']];
        $this->data += ['form-search-select-type' => $this->params['type_slug']];
        $this->data += ['form-search-select-ue_version' => $this->params['ue_version']];

        $this->data += ['blueprints' => $results['rows']];
        $this->data += ['current_page' => $this->params['page']];
        $this->data += ['count_items' => $results['count']];
        $this->data += ['count_per_page' => $this->params['count_per_page']];
        $this->data += ['title' => $this->getTitle()];
        $this->data += ['title_emphasis' => $this->getTitleEmphasis()];
        $this->data += ['type' => $this->getType()];
        $pagination = (new Pagination($this->configurationPagination))->generateHtml(
            $this->params['page'],
            $results['count'],
            $this->params['count_per_page']
        );
        $this->data += ['pagination' => $pagination];
        $this->data += ['show_links_other_types' => ($this->routeName === 'type-blueprints')];
        $this->data += ['show_search' => ($this->routeName === 'search')];

        return $this->sendPage();
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function findPageType(ServerRequestInterface $request): bool
    {
        $this->params['page'] = (int) $request->getAttribute('page');
        if ($this->params['page'] === 0) {
            $this->params['page'] = 1;
        }

        /* @noinspection NullPointerExceptionInspection */
        $this->routeName = Application::getRouter()->getCurrentRoute()->getCallback()->getCurrentRoute()->getName();
        if ($this->routeName === 'last-blueprints') {
            $this->treatRouteLastBlueprints();
        } elseif ($this->routeName === 'most-discussed-blueprints') {
            $this->treatRouteMostDiscussedBlueprints();
        } elseif ($this->routeName === 'type-blueprints') {
            $this->treatRouteTypeBlueprints($request);
        } elseif ($this->routeName === 'tag-blueprints') {
            $this->treatRouteTagBlueprints($request);
        } elseif ($this->routeName === 'search') {
            return $this->treatRouteSearch($request);
        }

        return true;
    }

    protected function treatRouteLastBlueprints(): void
    {
        $this->pageType = 'last';
        $this->configurationPagination['url'] = '/last-blueprints/{{PAGE}}/';

        $this->params['meta_title'] = 'Last blueprints';
        $this->params['meta_description'] = 'Last blueprints pasted';
    }

    protected function treatRouteMostDiscussedBlueprints(): void
    {
        $this->pageType = 'most-discussed';
        $this->configurationPagination['url'] = '/most-discussed-blueprints/{{PAGE}}/';

        $this->params['meta_title'] = 'Most discussed blueprints';
        $this->params['meta_description'] = 'Blueprints with the most comments';
    }

    protected function treatRouteTypeBlueprints(ServerRequestInterface $request): void
    {
        $this->pageType = 'type';
        $this->params['type_slug'] = $request->getAttribute('type');
        $this->configurationPagination['url'] = '/type/' . $this->params['type_slug'] . '/{{PAGE}}/';

        if ($this->params['type_slug'] === 'blueprint') {
            $this->params['meta_title'] = 'Blueprints';
        } else {
            $this->params['meta_title'] = \ucwords(\str_replace('-', ' ', $this->params['type_slug'])) . ' blueprints';
        }
        $this->params['meta_description'] = 'List of blueprints categorized as ' . \str_replace('-', ' ', $this->params['type_slug']);

        $this->params['type'] = $this->params['type_slug'];
        if ($this->params['type'] === 'behavior-tree') {
            $this->params['type'] = 'behavior_tree';
        }
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function treatRouteTagBlueprints(ServerRequestInterface $request): void
    {
        $this->pageType = 'tag';
        $this->params['tag_slug'] = $request->getAttribute('tag_slug');

        $this->params['tag'] = TagService::findTagWithSlug($this->params['tag_slug']);

        $this->configurationPagination['url'] = '/tag/' . $this->params['tag_slug'] . '/{{PAGE}}/';

        $tagTitle = ($this->params['tag'] !== null) ? $this->params['tag']['name'] : $this->params['tag_slug'];
        $this->params['meta_title'] = 'Tag ' . $tagTitle;
        $this->params['meta_description'] = 'List of blueprints tagged as ' . $tagTitle;
    }

    protected function treatRouteSearch(ServerRequestInterface $request): bool
    {
        $this->pageType = 'search';
        $queryParams = $request->getQueryParams();
        $urlParts = [];

        // avoid bad encoding string
        try {
            foreach ($queryParams as $queryParam) {
                Security::escHTML($queryParam);
            }
        } catch (\Exception $e) {
            return false;
        }

        $this->params['query'] = '';
        if (isset($queryParams['query'])) {
            $this->params['query'] = Helper::trim($queryParams['query']);
        } elseif (isset($queryParams['form-search-input-query'])) {
            $this->params['query'] = Helper::trim($queryParams['form-search-input-query']);
        }
        if ($this->params['query'] !== '') {
            $urlParts[] = 'form-search-input-query=' . $this->params['query'];
        }

        $this->params['type_slug'] = '';
        if (isset($queryParams['form-search-select-type']) && \in_array($queryParams['form-search-select-type'], ['animation', 'behavior-tree', 'blueprint', 'material', 'metasound', 'niagara', 'pcg'], true)) {
            $this->params['type_slug'] = Helper::trim($queryParams['form-search-select-type']);
            $urlParts[] = 'form-search-select-type=' . $this->params['type_slug'];

            $this->params['type'] = $this->params['type_slug'];
            if ($this->params['type'] === 'behavior-tree') {
                $this->params['type'] = 'behavior_tree';
            }
        }

        $this->params['ue_version'] = '';
        if (isset($queryParams['form-search-select-ue_version']) && \in_array($queryParams['form-search-select-ue_version'], Helper::getAllUEVersion(), true)) {
            $this->params['ue_version'] = Helper::trim($queryParams['form-search-select-ue_version']);
            $urlParts[] = 'form-search-select-ue_version=' . $this->params['ue_version'];
        }

        if (isset($queryParams['page'])) {
            $this->params['page'] = (int) $queryParams['page'];
            if ($this->params['page'] === 0) {
                $this->params['page'] = 1;
            }
        }

        $urlParts[] = 'page={{PAGE}}';

        $this->configurationPagination['url'] = '/search/';
        $this->configurationPagination['url'] .= '?' . \implode('&', $urlParts);

        $this->params['meta_title'] = 'Search "' . $this->params['query'] . '"';
        $this->params['meta_description'] = 'Search "' . $this->params['query'] . '" in blueprints pasted';

        return true;
    }

    protected function getTitle(): string
    {
        $title = 'Last pasted ';

        if ($this->pageType === 'search') {
            $title = 'Search Results ';
        } elseif ($this->pageType === 'most-discussed') {
            $title = 'Most discussed ';
        } elseif ($this->pageType === 'type') {
            $title = 'Blueprint type: ';
        } elseif ($this->pageType === 'tag') {
            $title = 'Tag ';
        }

        return $title;
    }

    protected function getTitleEmphasis(): string
    {
        $titleEmphasis = 'blueprints';

        if ($this->pageType === 'search') {
            $titleEmphasis = $this->params['query'] ?? '';
        } elseif ($this->pageType === 'most-discussed') {
            $titleEmphasis = 'blueprints';
        } elseif ($this->pageType === 'type') {
            $titleEmphasis = $this->params['type_slug'];
        } elseif ($this->pageType === 'tag') {
            $titleEmphasis = ($this->params['tag'] !== null) ? $this->params['tag']['name'] : $this->params['tag_slug'];
        }

        return $titleEmphasis;
    }

    protected function getType(): ?string
    {
        if ($this->pageType === 'type') {
            return $this->params['type_slug'];
        }

        return null;
    }
}
