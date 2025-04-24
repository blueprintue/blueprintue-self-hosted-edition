<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\TemplateTrait;
use app\helpers\Helper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;

class StaticController implements MiddlewareInterface
{
    use TemplateTrait;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $pageID = $data['request']->getAttribute('pageID');

        $this->pageFile = \str_replace('-', '_', $pageID);
        $this->currentPageForNavBar = $this->pageFile;

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('static-pages', ['pageID' => $pageID]) ?? '';

        $siteName = (string) Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition');
        $titleBase = (string) Application::getConfig()->get('SITE_BASE_TITLE', '');

        if ($pageID === 'terms-of-service') {
            $this->title = 'Terms of Service for ' . $siteName . ' | ' . $titleBase;
            $this->description = 'Terms of Service for ' . $siteName;
        } elseif ($pageID === 'privacy-policy') {
            $this->title = 'Privacy Policy for ' . $siteName . ' | ' . $titleBase;
            $this->description = 'Privacy Policy for ' . $siteName;
        }
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->setTemplateProperties(['request' => $request]);

        $this->data += ['site_name' => (string) Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')];
        $this->data += ['hostname' => Helper::getHostname()];
        $this->data += ['domain' => (string) Application::getConfig()->get('HOST')];
        $this->data += ['cookie_remember_token' => (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token')];
        $this->data += ['contact_email' => (string) Application::getConfig()->get('MAIL_CONTACT_TO', '')];

        return $this->sendPage();
    }
}
