<?php

declare(strict_types=1);

namespace app\controllers;

use app\helpers\FormHelper;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Http\Message\Stream;
use Rancoud\Session\Session;

trait TemplateTrait
{
    /** @var string use file name inside views/www/pages */
    protected string $pageFile;

    protected string $currentPageForNavBar;

    /** @var array data for views */
    protected array $data = [];

    protected string $title;

    protected string $description;

    protected string $url;

    protected string $locale = 'en_US';

    protected array $configurationPagination = [
        'count_pages_pair_limit' => 1,
        'dot_attrs'              => 'class="pagination__dot"',
        'item_attrs'             => 'class="pagination__item"',
        'item_attrs_current'     => 'class="pagination__item pagination__item--current"',
        'item_dots_attrs'        => 'class="pagination__item pagination__item--dot"',
        'item_next_attrs'        => 'class="pagination__item"',
        'item_previous_attrs'    => 'class="pagination__item"',
        'link_attrs'             => 'class="pagination__link"',
        'link_attrs_current'     => 'class="pagination__link pagination__link--current"',
        'nav_attrs'              => 'class="pagination"',
        'root_attrs'             => 'class="pagination__items"',
        'use_dots'               => true,
        'use_next'               => true,
        'use_previous'           => true,
    ];

    protected bool $isToHideFromGoogle = false;

    /**
     * @return string
     */
    protected function noRobotsIndex(): string
    {
        return $this->isToHideFromGoogle ? '<meta name="robots" content="noindex">' : '';
    }

    /**
     * @param array $data
     */
    abstract protected function setTemplateProperties(array $data = []): void;

    /**
     * @param string $file
     * @param array  $data
     *
     * @throws \Rancoud\Application\ApplicationException
     *
     * @return string
     */
    protected function getFullTemplate(string $file, array $data = []): string
    {
        \ob_start();
        require Application::getFolder('VIEWS') . 'www/parts/header.php';
        require Application::getFolder('VIEWS') . 'www/pages/' . $file . '.php';

        return \ob_get_clean() . '</html>';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendPage(): \Psr\Http\Message\ResponseInterface
    {
        $this->addCurrentPageForNavBarData();
        $this->addFormLoginData();
        $this->addFormRegisterData();
        $this->addFormLogoutData();
        $this->addFormForgotPasswordData();

        $page = $this->getFullTemplate($this->pageFile, $this->data);

        return (new Factory())->createResponse()->withBody(Stream::create($page));
    }

    /**
     * @param $url
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function redirect($url): \Psr\Http\Message\ResponseInterface
    {
        return (new Factory())->createResponse(301)->withHeader('Location', $url);
    }

    protected function addCurrentPageForNavBarData(): void
    {
        $this->data += ['navbar_current_page' => $this->currentPageForNavBar];
    }

    /**
     * @throws \Exception
     */
    protected function addFormLoginData(): void
    {
        if (Session::has('userID') === true) {
            return;
        }

        $formLogin = new FormHelper();
        $formLogin->setInputsErrors(Session::getFlash('form-login-errors'));
        $formLogin->setErrorMessage(Session::getFlash('error-form-login'));
        $this->data += ['form-login' => $formLogin];

        $this->data += ['form-login-hidden-csrf' => (string) Session::get('csrf')];
    }

    /**
     * @throws \Exception
     */
    protected function addFormRegisterData(): void
    {
        if (Session::has('userID') === true) {
            return;
        }

        $formRegister = new FormHelper();
        $formRegister->setInputsValues(Session::getFlash('form-register-values'));
        $formRegister->setInputsErrors(Session::getFlash('form-register-errors'));
        $formRegister->setErrorMessage(Session::getFlash('error-form-register'));
        $this->data += ['form-register' => $formRegister];

        $this->data += ['form-register-hidden-csrf' => (string) Session::get('csrf')];
    }

    /**
     * @throws \Exception
     */
    protected function addFormLogoutData(): void
    {
        if (Session::has('userID') === false) {
            return;
        }

        $this->data += ['form-logout-hidden-csrf' => (string) Session::get('csrf')];
    }

    /**
     * @throws \Exception
     */
    protected function addFormForgotPasswordData(): void
    {
        if (Session::has('userID') === true) {
            return;
        }

        $formForgotPassword = new FormHelper();
        $formForgotPassword->setInputsValues(Session::getFlash('form-forgot_password-values'));
        $formForgotPassword->setInputsErrors(Session::getFlash('form-forgot_password-errors'));
        $formForgotPassword->setErrorMessage(Session::getFlash('error-form-forgot_password'));
        $this->data += ['form-forgot_password' => $formForgotPassword];

        $this->data += ['form-forgot_password-hidden-csrf' => (string) Session::get('csrf')];
    }
}
