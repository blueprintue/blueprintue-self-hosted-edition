<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\TemplateTrait;
use app\helpers\Helper;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

class ConfirmAccountController implements MiddlewareInterface
{
    use TemplateTrait;

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'confirm_account';
        $this->currentPageForNavBar = 'confirm-account';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('confirm-account');

        $this->title = 'Confirm Account | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $this->description = 'Confirm Account';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Session::has('userID')) {
            return $this->redirect('/');
        }

        $this->setTemplateProperties();

        $isConfirmedAccount = null;
        $confirmedToken = $request->getQueryParams()['confirmed_token'] ?? null;
        if ($confirmedToken !== null) {
            // avoid bad encoding string
            try {
                Security::escHTML($confirmedToken);
            } catch (\Exception $e) {
                return $this->redirect('/');
            }

            $isConfirmedAccount = $this->checkConfirmedToken($confirmedToken);
        }

        $this->data += ['is_confirmed_account' => $isConfirmedAccount];
        $this->data += ['site_name' => Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')];

        return $this->sendPage();
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function checkConfirmedToken(string $confirmedToken): bool
    {
        return UserService::validateAccountWithConfirmedToken($confirmedToken);
    }
}
