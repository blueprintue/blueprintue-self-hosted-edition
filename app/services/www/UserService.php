<?php

declare(strict_types=1);

namespace app\services\www;

use app\helpers\Helper;
use app\helpers\MailerHelper;
use app\models\UserApiModel;
use app\models\UserInfosModel;
use app\models\UserModel;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Exception;
use Rancoud\Application\Application;
use Rancoud\Crypt\Crypt;
use Rancoud\Security\Security;

class UserService
{
    protected static int $minLenPasword = 10;
    protected static string $regexPassword = "/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^a-zA-Z0-9\s:])([^\s]){8,}$/";

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function findUserIDWithUsernameAndPassword(string $username, string $password): ?int
    {
        $userModel = (new UserModel(Application::getDatabase()));

        return $userModel->findUserIDWithUsernameAndPassword($username, $password);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getInfosForSession(int $userID): ?array
    {
        $userModel = (new UserModel(Application::getDatabase()));

        return $userModel->getInfosForSession($userID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public static function generateRememberToken(int $userID): string
    {
        $userModel = (new UserModel(Application::getDatabase()));

        do {
            $newRememberToken = Crypt::getRandomString(255);
            $otherUserID = $userModel->getUserIDFromRememberMe($newRememberToken);
        } while ($otherUserID !== null);

        $userModel->update(['remember_token' => $newRememberToken], $userID);

        return $newRememberToken;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function deleteRememberToken(int $userID): void
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $userModel->deleteRememberToken($userID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function getUserIDFromRememberMe(string $rememberToken): ?int
    {
        $userModel = (new UserModel(Application::getDatabase()));

        return $userModel->getUserIDFromRememberMe($rememberToken);
    }

    public static function getMinLengthPassword(): int
    {
        return static::$minLenPasword;
    }

    public static function isPasswordMatchFormat(string $password): bool
    {
        return \preg_match(static::$regexPassword, $password) === 1;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function isUsernameAvailable(string $username): bool
    {
        $user = (new UserModel(Application::getDatabase()));

        return $user->isUsernameAvailable($username, static::slugify($username));
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function isEmailAvailable(string $email): bool
    {
        $user = (new UserModel(Application::getDatabase()));

        return $user->isEmailAvailable($email);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function createMemberUser(string $username, string $email, string $password): array
    {
        $errorCode = '#100';

        $userModel = (new UserModel(Application::getDatabase()));
        $userInfosModel = (new UserInfosModel(Application::getDatabase()));

        $forceRollback = false;
        $userID = 0;
        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $errorCode = '#200';
            $userID = $userModel->create(
                [
                    'username'   => $username,
                    'slug'       => static::slugify($username),
                    'email'      => $email,
                    'grade'      => 'member',
                    'password'   => $password,
                    'created_at' => Helper::getNowUTCFormatted()
                ]
            );

            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because user requirements has been done before
             * For covering we have to test the function outside
             */
            if ($userID === 0) {
                throw new \Exception('User ID is nil');
            }
            // @codeCoverageIgnoreEnd

            $errorCode = '#300';
            $userInfosModel->create(['id_user' => $userID]);
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            $forceRollback = true;

            /*
             * In end 2 end testing we can't arrive here because user requirements has been done before
             * For covering we have to test the function outside
             */
            return [null, $errorCode];
            // @codeCoverageIgnoreEnd
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because user requirements has been done before
                 * For covering we have to mock the database
                 */
                Application::getDatabase()->rollbackTransaction();
            // @codeCoverageIgnoreEnd
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return [$userID, null];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function getPublicProfileInfos(string $username): ?array
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $infos = $userModel->getUserBySlugForPublicProfile($username);
        if (empty($infos)) {
            return null;
        }

        $userInfosModel = (new UserInfosModel(Application::getDatabase()));
        $moreInfos = $userInfosModel->one($infos['id']);
        if (!empty($moreInfos)) {
            $infos += $moreInfos;
        } else {
            $infos += $userInfosModel->getDefaultUsersInfos($infos['id']);
        }

        return $infos;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function getPrivateProfileInfos(int $userID): ?array
    {
        $user = (new UserModel(Application::getDatabase()));
        $infos = $user->getUserByIDForPrivateProfile($userID);
        if (empty($infos)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check on user has been done
             * For covering we have to test service only
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        $userInfos = (new UserInfosModel(Application::getDatabase()));
        $moreInfos = $userInfos->one($userID);
        if (!empty($moreInfos)) {
            $infos += $moreInfos;
        } else {
            $infos += $userInfos->getDefaultUsersInfos($userID);
        }

        $userApiModel = new UserApiModel(Application::getDatabase());
        $infos['api_key'] = $userApiModel->getApiKey($userID);

        return $infos;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function deleteUser($userID): bool
    {
        $forceRollback = false;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $userModel = (new UserModel(Application::getDatabase()));
            $userModel->delete($userID);

            $userInfosModel = (new UserInfosModel(Application::getDatabase()));
            $userInfosModel->delete($userID);

            $userApiModel = (new UserApiModel(Application::getDatabase()));
            $userApiModel->delete($userID);
        } catch (\Exception $exception) {
            $forceRollback = true;

            return false;
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return true;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateAvatar(int $id, ?string $filename): bool
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $user = $userModel->one($id);
        if (empty($user)) {
            return false;
        }

        if ($user['avatar'] !== null && \preg_match('/^[a-zA-Z0-9]{60}\.png$/D', $user['avatar']) === 1) {
            $filepath = Application::getFolder('MEDIAS_AVATARS') . $user['avatar'];
            if (\file_exists($filepath) && \is_file($filepath)) {
                \unlink($filepath);
            }
        }

        $userModel->update(['avatar' => $filename], $id);

        return true;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateBasicInfos(int $userID, ?string $bio, ?string $website): void
    {
        $fields = [
            'bio'          => $bio,
            'link_website' => $website
        ];

        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $infos = $userInfosModel->one($userID);
        if (empty($infos)) {
            $fields['id_user'] = $userID;
            $userInfosModel->create($fields);
        } else {
            $userInfosModel->update($fields, $userID);
        }
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateSocials(int $userID, array $socials): void
    {
        $fields = [];
        $props = ['facebook', 'twitter', 'github', 'youtube', 'twitch', 'unreal'];
        foreach ($props as $prop) {
            if (isset($socials[$prop])) {
                $fields['link_' . $prop] = $socials[$prop];
            }
        }

        if (\count($fields) === 0) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check on user has been done
             * For covering we have to test service only
             */
            return;
            // @codeCoverageIgnoreEnd
        }

        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $infos = $userInfosModel->one($userID);
        if (empty($infos)) {
            $fields['id_user'] = $userID;
            $userInfosModel->create($fields);
        } else {
            $userInfosModel->update($fields, $userID);
        }
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateEmail(int $userID, string $email): void
    {
        (new UserModel(Application::getDatabase()))->update(['email' => $email], $userID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateUsername(int $userID, string $username): void
    {
        (new UserModel(Application::getDatabase()))->update(['username' => $username, 'slug' => static::slugify($username)], $userID); // phpcs:ignore
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updatePassword(int $userID, string $password): void
    {
        (new UserModel(Application::getDatabase()))->update(['password' => $password], $userID);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Exception
     */
    protected static function getNewApiKey(UserApiModel $userApiModel): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $countCharacters = \mb_strlen($characters) - 1;

        do {
            $apiKey = '';

            for ($i = 0; $i < 35; ++$i) {
                $apiKey .= $characters[\random_int(0, $countCharacters)];
            }

            $apiKeyAvailable = $userApiModel->isApiKeyAvailable($apiKey);
        } while (!$apiKeyAvailable);

        return $apiKey;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function generateApiKey(int $userID): void
    {
        $userApiModel = new UserApiModel(Application::getDatabase());

        $apiKey = static::getNewApiKey($userApiModel);

        $userApiModel->delete($userID);
        $userApiModel->create(['id_user' => $userID, 'api_key' => $apiKey]);
    }

    public static function slugify(string $string): string
    {
        $string = Helper::trim($string);
        $string = \mb_strtolower($string);
        $string = \str_replace(['.', ' ', '@'], ['-', '-', ''], $string);

        return \preg_replace(['/([--]{2,})/', '/^-/', '/-$/'], ['-', '', ''], $string);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     *
     * @return array [?string $token, bool $userFound, ?string $username]
     */
    public static function beginResetPasswordProcess(string $email): array
    {
        $userModel = new UserModel(Application::getDatabase());
        $user = (new UserModel(Application::getDatabase()))->findUserWithEmailForResetPassword($email);
        if ($user === null || $user['id'] === (int) Application::getConfig()->get('ANONYMOUS_ID')) {
            return [null, false, null];
        }

        if ($user['password_reset_at'] !== null) {
            $nowTimestamp = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            $actionDoneTimestamp = (new DateTime($user['password_reset_at'], new DateTimeZone('UTC')))->getTimestamp();
            if (($nowTimestamp - $actionDoneTimestamp) < 300) {
                return [null, true, null];
            }
        }

        $now = Helper::getNowUTCFormatted();
        $token = Helper::getRandomString(128);
        $userModel->update(['password_reset' => $token, 'password_reset_at' => $now], $user['id']);

        return [$token, true, $user['username']];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function findUserIDFromEmailAndToken(string $email, string $token): ?int
    {
        return (new UserModel(Application::getDatabase()))->findUserIDFromEmailAndToken($email, $token);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function resetPassword(int $userID, string $password): void
    {
        (new UserModel(Application::getDatabase()))->update(['password' => $password, 'password_reset' => null, 'password_reset_at' => null], $userID); // phpcs:ignore
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePublicAndPrivateCommentCountWithComments(array $comments): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePublicAndPrivateCommentCountWithComments($comments);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePrivateCommentCountWithComments(array $comments): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePrivateCommentCountWithComments($comments);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getInfosFromIdAuthorIndex(array $items): array
    {
        return (new UserModel(Application::getDatabase()))->getInfosFromIdAuthorIndex($items);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePublicAndPrivateBlueprintCount(int $userID, int $count): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePublicAndPrivateBlueprintCount($userID, $count);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePrivateBlueprintCount(int $userID, int $count): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePrivateBlueprintCount($userID, $count);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePublicAndPrivateCommentCount(int $userID, int $count): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePublicAndPrivateCommentCount($userID, $count);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updatePrivateCommentCount(int $userID, int $count): void
    {
        $userInfosModel = new UserInfosModel(Application::getDatabase());
        $userInfosModel->updatePrivateCommentCount($userID, $count);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     *
     * @return array [bool $isConfirmedAccount, bool $hasToSendEmail]
     */
    public static function isUserConfirmedAccount(int $userID): array
    {
        $isConfirmedAccount = true;
        $hasToSendEmail = false;

        $user = (new UserModel(Application::getDatabase()))->one($userID);
        if (empty($user)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check on user has been done
             * For covering we have to test service only
             */
            return [false, $hasToSendEmail];
            // @codeCoverageIgnoreEnd
        }

        if ($user['confirmed_at'] !== null) {
            return [$isConfirmedAccount, $hasToSendEmail];
        }

        $isConfirmedAccount = false;

        if ($user['confirmed_sent_at'] === null) {
            $hasToSendEmail = true;
        } else {
            $nowTimestamp = (new DateTime('now', new DateTimeZone('UTC')))->getTimestamp();
            $actionDoneTimestamp = (new DateTime($user['confirmed_sent_at'], new DateTimeZone('UTC')))->getTimestamp();
            if (($nowTimestamp - $actionDoneTimestamp) > 300) {
                $hasToSendEmail = true;
            }
        }

        return [$isConfirmedAccount, $hasToSendEmail];
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public static function generateAndSendConfirmAccountEmail(int $userID, string $from): bool
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $user = $userModel->one($userID);
        if (empty($user)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check on user has been done
             * For covering we have to test service only
             */
            return false;
            // @codeCoverageIgnoreEnd
        }

        $token = $user['confirmed_token'] ?? Helper::getRandomString(255);

        $isSent = static::sendConfirmAccountEmail($user['email'], $token, $from, $user['username']);
        if ($isSent) {
            $userModel->update(['confirmed_token' => $token, 'confirmed_sent_at' => Helper::getNowUTCFormatted()], $userID); // phpcs:ignore
        }

        return $isSent;
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Security\SecurityException
     */
    protected static function sendConfirmAccountEmail(string $email, string $token, string $from, string $username): bool // phpcs:ignore
    {
        $subject = 'Confirm your account for ' . Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition'); // phpcs:ignore
        $html = static::getConfirmAccountEmailHTML($token, $username);
        $text = 'Welcome to ' . Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition') . "\n\n";
        $text .= 'We are excited to have you on board!' . "\n";
        $text .= 'To get started ' . $username . ', please copy the URL below to confirm your account:' . "\n\n";
        $text .= Helper::getHostname() . Application::getRouter()->generateUrl('confirm-account') . '?confirmed_token=' . $token . "\n"; // phpcs:ignore

        // only use for phpunit
        if (\function_exists('\tests\isPHPUnit')) {
            if ($from === 'login') {
                return \tests\www\Login\LoginTest::mailForPHPUnit($email, $subject, $html, $text, $token, Application::getDatabase()); // phpcs:ignore
            }

            if ($from === 'register') {
                return \tests\www\Register\RegisterTest::mailForPHPUnit($email, $subject, $html, $text, $token, Application::getDatabase()); // phpcs:ignore
            }

            // @codeCoverageIgnoreStart
            throw new Exception('Missing "from" implementation');
            // @codeCoverageIgnoreEnd
        }

        // @codeCoverageIgnoreStart
        /*
         * coverage is blocked by the function above
         */
        $mailer = new MailerHelper(true);
        $mailer->setHTMLEmail($subject, $html, $text);

        return $mailer->send($email);
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Security\SecurityException
     * @throws \Exception
     */
    protected static function getConfirmAccountEmailHTML(string $token, string $username): string
    {
        $url = Helper::getHostname() . Application::getRouter()->generateUrl('confirm-account') . '?confirmed_token=' . $token; // phpcs:ignore
        \ob_start();
        require Application::getFolder('VIEWS') . 'emails/confirm_account.html';

        $html = \ob_get_clean();

        $now = new DateTime('now', new DateTimeZone('UTC'));

        $search = [
            '{{TOKEN}}',
            '{{URL}}',
            '{{YEAR}}',
            '{{SITE_NAME_HTML}}',
            '{{SITE_NAME_ATTR}}',
            '{{USERNAME}}',
            '{{MAIL_HEADER_LOGO_PATH}}',
        ];

        $replace = [
            $token,
            $url,
            $now->format('Y'),
            Security::escHTML(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')),
            Security::escAttr(Application::getConfig()->get('SITE_NAME', 'blueprintUE self-hosted edition')),
            Security::escHTML($username),
            Security::escAttr(Helper::getHostname() . '/' . Security::escAttr(Application::getConfig()->get('MAIL_HEADER_LOGO_PATH', 'blueprintue-self-hosted-edition_logo-full.png'))), // phpcs:ignore
        ];

        return \str_replace($search, $replace, $html);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public static function validateAccountWithConfirmedToken(string $confirmedToken): bool
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $userID = $userModel->findUserIDWithConfirmedToken($confirmedToken);
        if ($userID === null) {
            return false;
        }

        $userModel->update(['confirmed_token' => null, 'confirmed_at' => Helper::getNowUTCFormatted()], $userID);

        return true;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function findUserIDWithApiKey(string $apiKey): ?int
    {
        return (new UserApiModel(Application::getDatabase()))->getUserID($apiKey);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     * @throws \Exception
     */
    public static function saveLastLogin(int $userID): bool
    {
        $userModel = (new UserModel(Application::getDatabase()));
        $user = $userModel->one($userID);
        if (empty($user)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check user ID has been done in the previous code
             * For covering we have to test the function outside
             */
            return false;
            // @codeCoverageIgnoreEnd
        }

        $now = Helper::getNowUTCFormatted();

        $userModel->update(['last_login_at' => $now], $userID);

        return true;
    }
}
