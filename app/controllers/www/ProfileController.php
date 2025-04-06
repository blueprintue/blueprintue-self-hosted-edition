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
use Rancoud\Pagination\Pagination;
use Rancoud\Session\Session;

class ProfileController implements MiddlewareInterface
{
    use TemplateTrait;

    protected int $countBlueprintsPerPage = 15;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'profile';
        $this->currentPageForNavBar = 'profile';

        $url = Helper::getHostname() . Application::getRouter()->generateUrl('profile', ['profile_slug' => $data['slug']]) ?? ''; // phpcs:ignore
        if ($data['page'] > 1) {
            $url .= '?page=' . $data['page'];
        }
        $this->url = $url;

        $this->title = 'Profile of ' . $data['username'] . ' | Page ' . $data['page'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', ''); // phpcs:ignore

        $this->description = 'Profile of ' . $data['username'];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Pagination\PaginationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $userInfos = $this->getUser($request);
        if ($userInfos === null) {
            return $this->redirect('/');
        }

        $this->completeViewProfile($request, $userInfos);
        if ($this->data['blueprints'] === null && $this->data['page'] > 1) {
            return $this->redirect(Application::getRouter()->generateUrl('profile', ['profile_slug' => $request->getAttribute('profile_slug')])); // phpcs:ignore
        }

        $this->setTemplateProperties([
            'username' => $this->data['username'],
            'slug'     => $request->getAttribute('profile_slug'),
            'page'     => $this->data['page'],
        ]);

        return $this->sendPage();
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function getUser(ServerRequestInterface $request): ?array
    {
        $profileSlug = $request->getAttribute('profile_slug');

        return UserService::getPublicProfileInfos($profileSlug);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Pagination\PaginationException
     * @throws \Exception
     */
    protected function completeViewProfile(ServerRequestInterface $request, array $userInfos): void
    {
        $canEdit = false;
        $key = 'public';
        if (Session::get('userID') === $userInfos['id_user']) {
            $key = 'private';
            $canEdit = true;
        }

        // profile
        $counters = [
            'blueprints' => $userInfos['count_' . $key . '_blueprint'],
            'comments'   => $userInfos['count_' . $key . '_comment']
        ];
        $labels = [
            'blueprints' => ($userInfos['count_' . $key . '_blueprint'] < 2) ? 'blueprint' : 'blueprints',
            'comments'   => ($userInfos['count_' . $key . '_comment'] < 2) ? 'comment' : 'comments'
        ];
        $links = [
            'website'  => $this->completeLink('website', $userInfos['link_website'] ?? ''),
            'facebook' => $this->completeLink('facebook', $userInfos['link_facebook']),
            'twitter'  => $this->completeLink('twitter', $userInfos['link_twitter']),
            'github'   => $this->completeLink('github', $userInfos['link_github']),
            'twitch'   => $this->completeLink('twitch', $userInfos['link_twitch']),
            'youtube'  => $this->completeLink('youtube', $userInfos['link_youtube']),
            'unreal'   => $this->completeLink('unreal', $userInfos['link_unreal']),
        ];

        $this->data += ['username' => $userInfos['username']];
        $this->data += ['avatar' => Helper::getAvatarUrl($userInfos['avatar'])];
        $this->data += ['can_edit' => $canEdit];
        $this->data += ['counters' => $counters];
        $this->data += ['labels' => $labels];
        $this->data += ['bio' => $userInfos['bio']];
        $this->data += ['website' => $userInfos['link_website'] ?? ''];
        $this->data += ['links' => $links];

        // blueprints
        $page = 1;
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['page'])) {
            $queryParams['page'] = (int) $queryParams['page'];
            if ($queryParams['page'] > 0) {
                $page = $queryParams['page'];
            }
        }
        $blueprints = BlueprintService::getForProfile($userInfos['id_user'], Session::get('userID'), $page, $this->countBlueprintsPerPage); // phpcs:ignore
        if ($blueprints['count'] > 0) {
            $users = UserService::getInfosFromIdAuthorIndex($blueprints['rows']);
            foreach ($blueprints['rows'] as $key => $row) {
                $blueprints['rows'][$key]['thumbnail_url'] = Helper::getThumbnailUrl($row['thumbnail']);
                $blueprints['rows'][$key]['url'] = Helper::getBlueprintLink($row['slug']);
                $blueprints['rows'][$key]['author'] = Helper::formatUser($users[$row['id_author']]);
                $blueprints['rows'][$key]['since'] = Helper::getSince($row['published_at']);
            }
        }

        $this->configurationPagination['url'] = '/profile/' . $userInfos['slug'] . '/?page={{PAGE}}';
        $pagination = (new Pagination($this->configurationPagination))->generateHtml(
            $page,
            $blueprints['count'],
            $this->countBlueprintsPerPage
        );

        $this->data += ['blueprints' => $blueprints['rows']];
        $this->data += ['pagination' => $pagination];
        $this->data += ['title' => 'Last <span>blueprints</span>'];
        $this->data += ['page' => $page];
    }

    protected function completeLink(string $type, ?string $value): string
    {
        $link = '';

        if (empty($value)) {
            return $link;
        }

        switch ($type) {
            case 'facebook':
                $link = \sprintf('https://www.facebook.com/%s', $value);
                break;
            case 'twitter':
                $link = \sprintf('https://twitter.com/%s', $value);
                break;
            case 'github':
                $link = \sprintf('https://github.com/%s', $value);
                break;
            case 'twitch':
                $link = \sprintf('https://www.twitch.tv/%s', $value);
                break;
            case 'unreal':
                $link = \sprintf('https://forums.unrealengine.com/u/%s', $value);
                break;
            case 'youtube':
                $link = \sprintf('https://www.youtube.com/channel/%s', $value);
                break;
            case 'website':
                if (\mb_strpos($value, 'http://') === 0 || \mb_strpos($value, 'https://') === 0) {
                    return $value;
                }

                $link = \sprintf('https://%s', $value);
                break;
        }

        return $link;
    }
}
