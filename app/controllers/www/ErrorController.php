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
use Rancoud\Session\Session;

class ErrorController implements MiddlewareInterface
{
    use TemplateTrait;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'error';
        $this->currentPageForNavBar = 'error';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('error');

        $this->title = 'Error | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');
        $this->description = 'Error';
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errorMessage = Session::getFlash('error_message');
        if ($errorMessage === null) {
            return $this->redirect('/');
        }

        $this->setTemplateProperties();

        $this->data += ['error_message' => $errorMessage];

        return $this->sendPage();
    }
}
