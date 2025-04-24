<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\controllers\FormTrait;
use app\controllers\TemplateTrait;
use app\helpers\FormHelper;
use app\helpers\Helper;
use app\middlewares\LoginMiddleware;
use app\services\www\BlueprintService;
use app\services\www\CommentService;
use app\services\www\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Security\Security;
use Rancoud\Session\Session;

class ProfileEditController implements MiddlewareInterface
{
    use FormTrait;
    use TemplateTrait;

    protected int $userID = 0;
    protected string $profileEditURL = '/';

    protected array $formCsrfKeys = [
        'form-delete_avatar-hidden-csrf'      => 'delete_avatar',
        'form-edit_basic_infos-hidden-csrf'   => 'edit_basic_infos',
        'form-edit_socials-hidden-csrf'       => 'edit_socials',
        'form-change_email-hidden-csrf'       => 'change_email',
        'form-change_username-hidden-csrf'    => 'change_username',
        'form-change_password-hidden-csrf'    => 'change_password',
        'form-generate_api_key-hidden-csrf'   => 'generate_api_key',
        'form-regenerate_api_key-hidden-csrf' => 'regenerate_api_key',
        'form-delete_profile-hidden-csrf'     => 'delete_profile'
    ];

    protected array $inputs = [
        'delete_avatar' => [
            'CSRF' => 'form-delete_avatar-hidden-csrf'
        ],
        'edit_basic_infos' => [
            'bio'     => 'form-edit_basic_infos-textarea-bio',
            'website' => 'form-edit_basic_infos-input-website',
            'CSRF'    => 'form-edit_basic_infos-hidden-csrf'
        ],
        'edit_socials' => [
            'facebook' => 'form-edit_socials-input-facebook',
            'twitter'  => 'form-edit_socials-input-twitter',
            'github'   => 'form-edit_socials-input-github',
            'youtube'  => 'form-edit_socials-input-youtube',
            'twitch'   => 'form-edit_socials-input-twitch',
            'unreal'   => 'form-edit_socials-input-unreal',
            'CSRF'     => 'form-edit_socials-hidden-csrf'
        ],
        'change_email' => [
            'new_email' => 'form-change_email-input-new_email',
            'CSRF'      => 'form-change_email-hidden-csrf'
        ],
        'change_username' => [
            'new_username' => 'form-change_username-input-new_username',
            'CSRF'         => 'form-change_username-hidden-csrf'
        ],
        'change_password' => [
            'new_password'         => 'form-change_password-input-new_password',
            'new_password_confirm' => 'form-change_password-input-new_password_confirm',
            'CSRF'                 => 'form-change_password-hidden-csrf'
        ],
        'generate_api_key' => [
            'CSRF'    => 'form-generate_api_key-hidden-csrf'
        ],
        'delete_profile' => [
            'blueprints_ownership' => 'form-delete_profile-select-blueprints_ownership',
            'comments_ownership'   => 'form-delete_profile-select-comments_ownership',
            'CSRF'                 => 'form-delete_profile-hidden-csrf'
        ]
    ];

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    protected function setTemplateProperties(array $data = []): void
    {
        $this->pageFile = 'profile_edit';
        $this->currentPageForNavBar = 'profile_edit';

        $this->url = Helper::getHostname() . Application::getRouter()->generateUrl('profile-edit', ['profile_slug' => $data['slug']]) ?? '';

        $this->title = 'Edit profile of ' . $data['username'] . ' | ' . Application::getConfig()->get('SITE_BASE_TITLE', '');

        $this->description = 'Edit profile of ' . $data['username'];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isAuthor($request) === false) {
            return $this->redirect(Application::getRouter()->generateUrl('profile', ['profile_slug' => $request->getAttribute('profile_slug')]));
        }

        $this->profileEditURL = Application::getRouter()->generateUrl('profile-edit', ['profile_slug' => $request->getAttribute('profile_slug')]) ?? '/';

        $response = $this->treatFormSent($request);
        if ($response !== null) {
            return $response;
        }

        $this->computeDataForRender($request);

        return $this->sendPage();
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function isAuthor(ServerRequestInterface $request): bool
    {
        $userID = Session::get('userID');
        if ($userID === null) {
            return false;
        }

        if ($userID === (int) Application::getConfig()->get('ANONYMOUS_ID')) {
            return false;
        }

        $profileSlug = $request->getAttribute('profile_slug');
        $infos = UserService::getPublicProfileInfos($profileSlug);
        if ($infos === null) {
            return false;
        }

        if ($infos['id'] === $userID) {
            $this->userID = $userID;
        }

        return $this->userID === $userID;
    }

    /** @throws \Exception */
    protected function treatFormSent(ServerRequestInterface $request): ?ResponseInterface
    {
        $formKey = $this->findFormSent($request);
        if ($formKey !== null) {
            switch ($formKey) {
                case 'delete_avatar':
                    return $this->doProcessDeleteAvatar();
                case 'edit_basic_infos':
                    $cleanedParams = $this->treatFormEditBasicInfos($request, $this->inputs['edit_basic_infos']);

                    return $this->doProcessEditBasicInfos($cleanedParams);
                case 'edit_socials':
                    $cleanedParams = $this->treatFormEditSocials($request, $this->inputs['edit_socials']);

                    return $this->doProcessEditSocials($cleanedParams);
                case 'change_email':
                    $cleanedParams = $this->treatFormChangeEmail($request, $this->inputs['change_email']);

                    return $this->doProcessChangeEmail($cleanedParams);
                case 'change_username':
                    $cleanedParams = $this->treatFormChangeUsername($request, $this->inputs['change_username']);

                    return $this->doProcessChangeUsername($cleanedParams);
                case 'change_password':
                    $cleanedParams = $this->treatFormChangePassword($request, $this->inputs['change_password']);

                    return $this->doProcessChangePassword($cleanedParams);
                case 'generate_api_key':
                    return $this->doProcessGenerateApiKey();
                case 'delete_profile':
                    $cleanedParams = $this->treatFormDeleteProfile($request, $this->inputs['delete_profile']);

                    return $this->doProcessDeleteProfile($cleanedParams);
            }
        }

        return null;
    }

    /** @throws \Exception */
    protected function findFormSent(ServerRequestInterface $request): ?string
    {
        if ($request->getMethod() !== 'POST') {
            return null;
        }

        $rawParams = $request->getParsedBody();

        $formKeyFound = null;
        foreach ($this->formCsrfKeys as $csrfFieldName => $formCsrfKey) {
            if (isset($rawParams[$csrfFieldName])) {
                $formKeyFound = $formCsrfKey;
                break;
            }
        }

        if ($formKeyFound === null) {
            return null;
        }

        $csrf = Session::get('csrf');
        if (empty($csrf) || !isset($rawParams[$this->inputs[$formKeyFound]['CSRF']]) || $csrf !== $rawParams[$this->inputs[$formKeyFound]['CSRF']]) {
            return null;
        }

        foreach ($this->inputs[$formKeyFound] as $key => $input) {
            if ($key === 'CSRF') {
                continue;
            }

            if (!isset($rawParams[$input])) {
                $this->setAndKeepInfos('error-form-' . $formKeyFound, 'Error, missing fields');

                return null;
            }

            // avoid bad encoding string
            try {
                Security::escHTML($rawParams[$input]);
            } catch (\Exception $e) {
                return null;
            }
        }

        return $formKeyFound;
    }

    protected function cleanRawParamsInRequest(ServerRequestInterface $request, array $inputs): array
    {
        $params = [];
        $htmlNames = \array_values($inputs);
        $rawParams = $request->getParsedBody();
        foreach ($rawParams as $key => $rawParam) {
            if (\in_array($key, $htmlNames, true)) {
                $params[$key] = Helper::trim($rawParam);
            }
        }

        return $params;
    }

    // region Delete Avatar
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessDeleteAvatar(): ResponseInterface
    {
        if (UserService::updateAvatar($this->userID, null)) {
            Session::setFlash('success-form-delete_avatar', 'Your avatar is now deleted');
            Session::keepFlash(['success-form-delete_avatar']);
        } else {
            // @codeCoverageIgnoreStart
            Session::setFlash('error-form-delete_avatar', 'Error, could not delete your avatar');
            Session::keepFlash(['error-form-delete_avatar']);
            // @codeCoverageIgnoreEnd
        }

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Edit Basic Infos
    protected function treatFormEditBasicInfos(ServerRequestInterface $request, array $inputs): array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $values = [];

        $values['bio'] = $params[$inputs['bio']];
        $values['website'] = $params[$inputs['website']];

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessEditBasicInfos(array $params): ResponseInterface
    {
        UserService::updateBasicInfos($this->userID, $params['bio'], $params['website']);

        Session::setFlash('success-form-edit_basic_infos', 'Your basic informations has been saved');
        Session::keepFlash(['success-form-edit_basic_infos']);

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Edit Socials
    /** @throws \Exception */
    protected function treatFormEditSocials(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        $values['facebook'] = $params[$inputs['facebook']];
        $values['twitter'] = $params[$inputs['twitter']];
        $values['github'] = $params[$inputs['github']];
        $values['youtube'] = $params[$inputs['youtube']];
        $values['twitch'] = $params[$inputs['twitch']];
        $values['unreal'] = $params[$inputs['unreal']];

        $fieldsRule = [
            'facebook' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'Facebook',
            ],
            'twitter' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'Twitter',
            ],
            'github' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'GitHub',
            ],
            'youtube' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'Youtube',
            ],
            'twitch' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'Twitch',
            ],
            'unreal' => [
                'regex' => '/^[a-zA-Z0-9._-]*$/D',
                'error' => 'Expected username containing: digits, letters, symbols: - _ .',
                'name'  => 'Unreal',
            ]
        ];

        foreach ($fieldsRule as $fieldRule => $params) {
            if (\preg_match($params['regex'], $values[$fieldRule]) === 0) {
                $errorsForMessage[] = $params['name'];
                $errors[$fieldRule] = $params['error'];
            }
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-edit_socials', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-edit_socials-errors', $errors);
            Session::setFlash('form-edit_socials-values', $values);
            Session::keepFlash(['error-form-edit_socials', 'form-edit_socials-errors', 'form-edit_socials-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessEditSocials(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->profileEditURL);
        }

        UserService::updateSocials($this->userID, $params);

        Session::setFlash('success-form-edit_socials', 'Your social profiles has been saved');
        Session::keepFlash(['success-form-edit_socials']);

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Change Email
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function treatFormChangeEmail(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // email
        $values['new_email'] = $params[$inputs['new_email']];
        if ($values['new_email'] === '') {
            $errorsForMessage[] = 'email';
            $errors['new_email'] = 'Email is required';
        } else {
            $posArobase = \mb_strpos($values['new_email'], '@');
            if ($posArobase < 1 || $posArobase === \mb_strlen($values['new_email']) - 1) {
                $errorsForMessage[] = 'email';
                $errors['new_email'] = 'Email is invalid';
            } elseif (!UserService::isEmailAvailable($values['new_email'])) {
                $errorsForMessage[] = 'email';
                $errors['new_email'] = 'Email is unavailable';
            }
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-change_email', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-change_email-errors', $errors);
            Session::setFlash('form-change_email-values', $values);
            Session::keepFlash(['error-form-change_email', 'form-change_email-errors', 'form-change_email-values']);

            return null;
        }

        return $values;
    }

    /** @throws \Exception */
    protected function doProcessChangeEmail(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->profileEditURL);
        }

        UserService::updateEmail($this->userID, $params['new_email']);

        Session::setFlash('success-form-change_email', 'Your new email has been saved');
        Session::keepFlash(['success-form-change_email']);

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Change Username
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function treatFormChangeUsername(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // username
        $values['new_username'] = $params[$inputs['new_username']];
        if ($values['new_username'] === '') {
            $errorsForMessage[] = 'new username';
            $errors['new_username'] = 'Username is required';
        } elseif (\preg_match('/^[a-zA-Z0-9._ -]*$/D', $values['new_username']) !== 1) {
            $errorsForMessage[] = 'new username';
            $errors['new_username'] = 'Username is invalid';
        } elseif (!UserService::isUsernameAvailable($values['new_username'])) {
            $errorsForMessage[] = 'new username';
            $errors['new_username'] = 'Username is unavailable';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-change_username', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-change_username-errors', $errors);
            Session::setFlash('form-change_username-values', $values);
            Session::keepFlash(['error-form-change_username', 'form-change_username-errors', 'form-change_username-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessChangeUsername(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->profileEditURL);
        }

        UserService::updateUsername($this->userID, $params['new_username']);

        Session::setFlash('success-form-change_username', 'Your new username has been saved');
        Session::keepFlash(['success-form-change_username']);

        (new LoginMiddleware())->login($this->userID);

        $newSlug = UserService::slugify($params['new_username']);

        return $this->redirect(Application::getRouter()->generateUrl('profile-edit', ['profile_slug' => $newSlug]));
    }
    // endregion

    // region Change Password
    /** @throws \Exception */
    protected function treatFormChangePassword(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // password
        $values['new_password'] = $params[$inputs['new_password']];
        $values['new_password_confirm'] = $params[$inputs['new_password_confirm']];
        $minLengthPassword = UserService::getMinLengthPassword();
        $errorLengthPassword = \sprintf('Password must be at least %d characters in length', $minLengthPassword);
        $pass1Len = \mb_strlen($values['new_password']);
        $pass2Len = \mb_strlen($values['new_password_confirm']);
        if ($pass1Len < $minLengthPassword && $pass2Len >= $minLengthPassword) {
            $errorsForMessage[] = 'new password';
            $errors['new_password'] = $errorLengthPassword;
        } elseif ($pass1Len >= $minLengthPassword && $pass2Len < $minLengthPassword) {
            $errorsForMessage[] = 'confirm new password';
            $errors['new_password_confirm'] = $errorLengthPassword;
        } elseif ($pass1Len < $minLengthPassword && $pass2Len < $minLengthPassword) {
            $errorsForMessage[] = 'new password';
            $errorsForMessage[] = 'confirm new password';
            $errors['new_password'] = $errorLengthPassword;
            $errors['new_password_confirm'] = $errorLengthPassword;
        } elseif ($values['new_password'] !== $values['new_password_confirm']) {
            $errorsForMessage[] = 'confirm new password';
            $errors['new_password_confirm'] = 'Confirm Password must be the same as Password';
        } elseif (!UserService::isPasswordMatchFormat($values['new_password'])) {
            $errorsForMessage[] = 'new password';
            $errors['new_password'] = 'Password must have 1 digit and 1 uppercase and 1 lowercase and 1 special characters';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-change_password', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-change_password-errors', $errors);
            Session::keepFlash(['error-form-change_password', 'form-change_password-errors']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessChangePassword(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->profileEditURL);
        }

        UserService::updatePassword($this->userID, $params['new_password']);

        Session::setFlash('success-form-change_password', 'Your new password has been saved');
        Session::keepFlash(['success-form-change_password']);

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Generate API KEY
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function doProcessGenerateApiKey(): ?ResponseInterface
    {
        UserService::generateApiKey($this->userID);

        Session::setFlash('success-form-generate_api_key', 'Your api key is now generated');
        Session::keepFlash(['success-form-generate_api_key']);

        return $this->redirect($this->profileEditURL);
    }
    // endregion

    // region Delete Profile
    /** @throws \Exception */
    protected function treatFormDeleteProfile(ServerRequestInterface $request, array $inputs): ?array
    {
        $params = $this->cleanRawParamsInRequest($request, $inputs);
        $errorsForMessage = [];
        $errors = [];
        $values = [];

        // blueprints_ownership
        $values['blueprints_ownership'] = $params[$inputs['blueprints_ownership']];
        if (!\in_array($values['blueprints_ownership'], ['give', 'delete'], true)) {
            $errorsForMessage[] = 'blueprints ownership';
            $errors['blueprints_ownership'] = 'Blueprints Ownership is invalid';
        }

        // comments_ownership
        $values['comments_ownership'] = $params[$inputs['comments_ownership']];
        if (!\in_array($values['comments_ownership'], ['keep', 'anonymize', 'delete'], true)) {
            $errorsForMessage[] = 'comments ownership';
            $errors['comments_ownership'] = 'Comments Ownership is invalid';
        }

        if (\count($errors) > 0) {
            Session::setFlash('error-form-delete_profile', 'Error(s) on ' . \implode(', ', $errorsForMessage));
            Session::setFlash('form-delete_profile-errors', $errors);
            Session::setFlash('form-delete_profile-values', $values);
            Session::keepFlash(['error-form-delete_profile', 'form-delete_profile-errors', 'form-delete_profile-values']);

            return null;
        }

        return $values;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected function doProcessDeleteProfile(?array $params): ?ResponseInterface
    {
        if ($params === null) {
            return $this->redirect($this->profileEditURL);
        }

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $anonymousID = (int) Application::getConfig()->get('ANONYMOUS_ID');

            if ($params['blueprints_ownership'] === 'give' && $anonymousID > 0) {
                BlueprintService::changeAuthor($this->userID, $anonymousID);
            } else {
                BlueprintService::softDeleteFromAuthor($this->userID);
            }

            if ($params['comments_ownership'] === 'keep') {
                $userInfos = UserService::getInfosForSession($this->userID);
                if ($userInfos === null || empty($userInfos['username'])) {
                    // @codeCoverageIgnoreStart
                    /*
                     * In end 2 end testing we can't arrive here because checks has been done before
                     * For covering we have to mock database
                     */
                    throw new \Exception('error on delete user');
                    // @codeCoverageIgnoreEnd
                }
                CommentService::keepComments($this->userID, $userInfos['username']);
            } elseif ($params['comments_ownership'] === 'anonymize') {
                CommentService::anonymizeComments($this->userID);
            } else {
                CommentService::deleteFromAuthor($this->userID);
            }

            if (UserService::deleteUser($this->userID) === false) {
                throw new \Exception('error on delete user');
            }

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->commitTransaction();

            \setcookie(
                (string) Application::getConfig()->get('SESSION_REMEMBER_NAME', 'remember_token'),
                '',
                [
                    'expires' => \time() - 3600 * 24 * 30,
                ]
            );

            Session::destroy();
        } catch (\Exception $e) {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->rollbackTransaction();

            Session::setFlash('error-form-delete_profile', 'Error, could not delete your profile');
            Session::keepFlash(['error-form-delete_profile']);

            return $this->redirect($this->profileEditURL);
        }

        return (new Factory())->createResponse(301)->withHeader('Location', '/');
    }
    // endregion

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function computeEditProfile(int $userID): void
    {
        $userInfos = UserService::getPrivateProfileInfos($userID);

        $this->data += ['profile_id' => $userInfos['id_user']];
        $this->data += ['username' => $userInfos['username']];
        $this->data += ['avatar' => Helper::getAvatarUrl($userInfos['avatar'])];
        $this->data += ['email' => $userInfos['email']];
        $this->data += ['slug' => $userInfos['slug']];
        $this->data += ['has_password' => $userInfos['has_password']];
        $this->data += ['api_key' => $userInfos['api_key']];

        $this->data += ['bio' => $userInfos['bio']];
        $this->data += ['website' => $userInfos['link_website']];

        $links = [
            'facebook' => $userInfos['link_facebook'],
            'twitter'  => $userInfos['link_twitter'],
            'github'   => $userInfos['link_github'],
            'twitch'   => $userInfos['link_twitch'],
            'youtube'  => $userInfos['link_youtube'],
            'unreal'   => $userInfos['link_unreal'],
        ];

        $this->data += ['links' => $links];
        $this->data += ['has_not_anonymous_user' => ((int) Application::getConfig()->get('ANONYMOUS_ID')) === 0];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    protected function computeDataForRender(ServerRequestInterface $request): void
    {
        $this->computeEditProfile($this->userID);

        $csrf = Session::get('csrf');

        $this->data += [$this->inputs['delete_avatar']['CSRF'] => $csrf];
        $this->data += [$this->inputs['edit_basic_infos']['CSRF'] => $csrf];
        $this->data += [$this->inputs['edit_socials']['CSRF'] => $csrf];
        $this->data += [$this->inputs['change_email']['CSRF'] => $csrf];
        $this->data += [$this->inputs['change_username']['CSRF'] => $csrf];
        $this->data += [$this->inputs['change_password']['CSRF'] => $csrf];
        $this->data += [$this->inputs['generate_api_key']['CSRF'] => $csrf];
        $this->data += [$this->inputs['delete_profile']['CSRF'] => $csrf];
        $this->data += ['data-uploader-upload-params-csrf' => $csrf];

        $formDeleteAvatar = new FormHelper();
        $formDeleteAvatar->setErrorMessage(Session::getFlash('error-form-delete_avatar'));
        $formDeleteAvatar->setSuccessMessage(Session::getFlash('success-form-delete_avatar'));
        $this->data += ['form-delete_avatar' => $formDeleteAvatar];

        $formEditBasicInfos = new FormHelper();
        $formEditBasicInfos->setInputValueIfEmpty('bio', $this->data['bio']);
        $formEditBasicInfos->setInputValueIfEmpty('website', $this->data['website']);
        $formEditBasicInfos->setErrorMessage(Session::getFlash('error-form-edit_basic_infos'));
        $formEditBasicInfos->setSuccessMessage(Session::getFlash('success-form-edit_basic_infos'));
        $this->data += ['form-edit_basic_infos' => $formEditBasicInfos];

        $formEditSocials = new FormHelper();
        $formEditSocials->setInputsValues(Session::getFlash('form-edit_socials-values'));
        $formEditSocials->setInputsErrors(Session::getFlash('form-edit_socials-errors'));
        $formEditSocials->setInputValueIfEmpty('facebook', $this->data['links']['facebook']);
        $formEditSocials->setInputValueIfEmpty('twitter', $this->data['links']['twitter']);
        $formEditSocials->setInputValueIfEmpty('github', $this->data['links']['github']);
        $formEditSocials->setInputValueIfEmpty('youtube', $this->data['links']['youtube']);
        $formEditSocials->setInputValueIfEmpty('twitch', $this->data['links']['twitch']);
        $formEditSocials->setInputValueIfEmpty('unreal', $this->data['links']['unreal']);
        $formEditSocials->setErrorMessage(Session::getFlash('error-form-edit_socials'));
        $formEditSocials->setSuccessMessage(Session::getFlash('success-form-edit_socials'));
        $this->data += ['form-edit_socials' => $formEditSocials];

        $formChangeEmail = new FormHelper();
        $formChangeEmail->setInputsValues(Session::getFlash('form-change_email-values'));
        $formChangeEmail->setInputsErrors(Session::getFlash('form-change_email-errors'));
        $formChangeEmail->setErrorMessage(Session::getFlash('error-form-change_email'));
        $formChangeEmail->setSuccessMessage(Session::getFlash('success-form-change_email'));
        $this->data += ['form-change_email' => $formChangeEmail];

        $formChangeUsername = new FormHelper();
        $formChangeUsername->setInputsValues(Session::getFlash('form-change_username-values'));
        $formChangeUsername->setInputsErrors(Session::getFlash('form-change_username-errors'));
        $formChangeUsername->setErrorMessage(Session::getFlash('error-form-change_username'));
        $formChangeUsername->setSuccessMessage(Session::getFlash('success-form-change_username'));
        $this->data += ['form-change_username' => $formChangeUsername];

        $formChangePassword = new FormHelper();
        $formChangePassword->setInputsErrors(Session::getFlash('form-change_password-errors'));
        $formChangePassword->setErrorMessage(Session::getFlash('error-form-change_password'));
        $formChangePassword->setSuccessMessage(Session::getFlash('success-form-change_password'));
        $this->data += ['form-change_password' => $formChangePassword];

        $formGenerateApiKey = new FormHelper();
        $formGenerateApiKey->setSuccessMessage(Session::getFlash('success-form-generate_api_key'));
        $this->data += ['form-generate_api_key' => $formGenerateApiKey];

        $formDeleteProfile = new FormHelper();
        $formDeleteProfile->setInputsValues(Session::getFlash('form-delete_profile-values'));
        $formDeleteProfile->setInputsErrors(Session::getFlash('form-delete_profile-errors'));
        $formDeleteProfile->setErrorMessage(Session::getFlash('error-form-delete_profile'));
        $this->data += ['form-delete_profile' => $formDeleteProfile];

        $this->setTemplateProperties([
            'username' => $this->data['slug'],
            'slug'     => $request->getAttribute('profile_slug'),
        ]);
    }
}
