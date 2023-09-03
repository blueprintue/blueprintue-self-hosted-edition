<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

use app\helpers\Helper;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class ProfileEditPOSTDeleteAvatarTest extends TestCase
{
    use Common;

    protected static function cleanMedias(): void
    {
        $ds = \DIRECTORY_SEPARATOR;
        $storageFolder = \dirname(__DIR__, 3) . $ds . 'medias' . $ds;
        $items = \array_diff(\scandir($storageFolder), ['..', '.']);
        foreach ($items as $item) {
            $fullpath = $storageFolder . $item;
            if (\is_file($fullpath)) {
                \unlink($fullpath);
            } elseif (\is_dir($fullpath)) {
                \rmdir($fullpath);
            }
        }
    }

    /**
     * @throws DatabaseException
     * @throws \Rancoud\Crypt\CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::cleanMedias();

        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 189,
            'username' => 'user_189',
            'hash'     => Crypt::hash('password_user_189'),
            'slug'     => 'user_189',
            'email'    => 'user_189@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 195,
            'username' => 'user_195',
            'hash'     => Crypt::hash('password_user_195'),
            'slug'     => 'user_195',
            'email'    => null,
            'grade'    => 'member',
            'avatar'   => 'formage.jpg',
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 199,
            'username' => 'user_199 <script>alert(1)</script>',
            'hash'     => Crypt::hash('password_user_199'),
            'slug'     => 'user_199',
            'email'    => 'user_199@example.com',
            'grade'    => 'member',
            'avatar'   => 'mem\"><script>alert(1)</script>fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        static::$db->insert("replace into users (id, username, password, slug, email, created_at) values (2, 'anonymous', null, 'anonymous', 'anonymous@mail', utc_timestamp())");
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function tearDownAfterClass(): void
    {
        static::cleanMedias();
    }

    /**
     * @throws \Exception
     */
    public function dataCasesDeleteAvatar(): array
    {
        $randomAvatarsName = [];
        for ($i = 0; $i < 2; ++$i) {
            do {
                $good = false;
                $name = Helper::getRandomString(60) . '.png';
                if (!\in_array($name, $randomAvatarsName, true)) {
                    $randomAvatarsName[] = $name;
                    $good = true;
                }
            } while (!$good);
        }

        return [
            'delete avatar OK - no avatar before' => [
                'sql_queries' => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp())"
                ],
                'user_id'     => 489,
                'params'      => [
                    'form-delete_avatar-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">Your avatar is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => null,
                'is_file'             => false,
            ],
            'delete avatar OK - has avatar before  (file is not in disk)' => [
                'sql_queries' => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp(), '<script>alert(1)</script>')"
                ],
                'user_id'     => 489,
                'params'      => [
                    'form-delete_avatar-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">Your avatar is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => null,
                'is_file'             => false,
            ],
            'delete avatar OK - has avatar before  (file is in disk)' => [
                'sql_queries' => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp(), '" . $randomAvatarsName[0] . "')"
                ],
                'user_id'     => 489,
                'params'      => [
                    'form-delete_avatar-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">Your avatar is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => $randomAvatarsName[0],
                'is_file'             => true,
            ],
            'delete avatar OK - has avatar before  (file is in disk as dir but not delete)' => [
                'sql_queries' => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp(), '" . $randomAvatarsName[1] . "')"
                ],
                'user_id'     => 489,
                'params'      => [
                    'form-delete_avatar-hidden-csrf' => 'csrf_is_replaced',
                ],
                'use_csrf_from_session' => true,
                'has_redirection'       => true,
                'is_form_success'       => true,
                'flash_messages'        => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">Your avatar is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => $randomAvatarsName[1],
                'is_file'             => false,
            ],
            'csrf incorrect' => [
                'sql_queries' => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp(), '<script>alert(1)</script>')"
                ],
                'user_id'     => 489,
                'params'      => [
                    'form-delete_avatar-hidden-csrf' => 'incorrect_csrf',
                ],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => null,
                'is_file'             => false,
            ],
            'missing fields - no csrf' => [
                'sql_queries'           => [
                    "REPLACE INTO users (id, username, password, slug, email, created_at, avatar) VALUES (489, 'user_489', null, 'user_489', null, utc_timestamp(), '<script>alert(1)</script>')"
                ],
                'user_id'               => 489,
                'params'                => [],
                'use_csrf_from_session' => false,
                'has_redirection'       => false,
                'is_form_success'       => false,
                'flash_messages'        => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_avatar">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_avatar" role="alert">'
                    ]
                ],
                'file_or_dir_on_disk' => null,
                'is_file'             => false,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesDeleteAvatar
     *
     * @param array       $sqlQueries
     * @param int         $userID
     * @param array       $params
     * @param bool        $useCsrfFromSession
     * @param bool        $hasRedirection
     * @param bool        $isFormSuccess
     * @param array       $flashMessages
     * @param string|null $fileOrDirOnDisk
     * @param bool        $isFile
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    public function testProfileEditPOSTDeleteAvatar(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, ?string $fileOrDirOnDisk, bool $isFile): void
    {
        static::setDatabase();

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // set user session
        $sessionValues = [
            'set'    => ['userID' => $userID],
            'remove' => []
        ];

        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-delete_avatar-hidden-csrf'] = $_SESSION['csrf'];
        }

        // avatar before
        $avatarBefore = static::$db->selectVar('SELECT avatar FROM users WHERE id = ' . $userID);

        $additionalsFolders = ['MEDIAS_AVATARS' => \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias'];

        if ($fileOrDirOnDisk !== null) {
            if ($isFile) {
                \file_put_contents(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk, 'aze');
            } else {
                \mkdir(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk);
            }
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params, [], [], [], [], $additionalsFolders);

        if ($hasRedirection) {
            static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        $avatarAfter = static::$db->selectVar('SELECT avatar FROM users WHERE id = ' . $userID);

        if ($isFormSuccess) {
            static::assertNull($avatarAfter);
        } else {
            static::assertSame($avatarBefore, $avatarAfter);
        }

        // test flash error message
        if ($flashMessages['error']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['error']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['error']['message']);
        }

        // test flash success message
        if ($flashMessages['success']['has']) {
            $this->doTestHtmlBody($response, $flashMessages['success']['message']);
        } else {
            $this->doTestHtmlBodyNot($response, $flashMessages['success']['message']);
        }

        if ($fileOrDirOnDisk !== null) {
            if ($isFile) {
                static::assertFileDoesNotExist(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk);
            } else {
                static::assertDirectoryExists(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk);
            }
        }
    }
}
