<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\TemplateTrait;
use app\helpers\Helper;
use app\services\www\BlueprintService;
use app\services\www\TagService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Session\Session;

class TagsController implements MiddlewareInterface
{
    use TemplateTrait;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'tags';
        $this->currentPageForNavBar = 'tags';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('tags') ?? '';

        $this->title = 'Blueprint\'s Tags | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');
        $this->description = 'List of tags associated to blueprints';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Session\SessionException
     * @throws \Rancoud\Database\DatabaseException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setTemplateProperties();

        $tagsPresentation = [];
        $tags = TagService::getAllTags();
        $tagIDsAvailable = BlueprintService::getTagsFromPublicBlueprints(Session::get('userID'));
        foreach ($tags as $tag) {
            if (!\in_array($tag['id'], $tagIDsAvailable, true)) {
                continue;
            }

            $firstChar = \mb_strtoupper(\mb_substr($tag['name'], 0, 1));
            if (!isset($tagsPresentation[$firstChar])) {
                $tagsPresentation[$firstChar] = [];
            }

            $tagsPresentation[$firstChar][] = $tag;
        }

        $this->data += ['tags' => $tagsPresentation];

        return $this->sendPage();
    }
}
