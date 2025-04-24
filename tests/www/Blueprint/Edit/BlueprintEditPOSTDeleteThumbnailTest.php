<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Blueprint\Edit;

use app\helpers\Helper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintEditPOSTDeleteThumbnailTest extends TestCase
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
     *
     * @return array[]
     */
    public static function dataCasesDeleteThumbnail(): array
    {
        $randomThumbailsName = [];
        for ($i = 0; $i < 2; ++$i) {
            do {
                $good = false;
                $name = Helper::getRandomString(60) . '.png';
                if (!\in_array($name, $randomThumbailsName, true)) {
                    $randomThumbailsName[] = $name;
                    $good = true;
                }
            } while (!$good);
        }

        return [
            'delete thumbnail OK - no thumbnail before' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_thumbnail-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">Blueprint thumbnail is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => null,
                'isFile'          => false,
            ],
            'delete thumbnail OK - has thumbnail before (file is not in disk)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '<script>alert(1)</script>')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_thumbnail-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">Blueprint thumbnail is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => null,
                'isFile'          => false,
            ],
            'delete thumbnail OK - has thumbnail before (file is in disk)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '" . $randomThumbailsName[0] . "')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_thumbnail-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">Blueprint thumbnail is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => $randomThumbailsName[0],
                'isFile'          => true,
            ],
            'delete thumbnail OK - has thumbnail before (file is in disk as dir but not delete)' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '" . $randomThumbailsName[1] . "')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_thumbnail-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">Blueprint thumbnail is now deleted</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => $randomThumbailsName[1],
                'isFile'          => false,
            ],
            'csrf incorrect' => [
                'sqlQueries' => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '<script>alert(1)</script>')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID' => 189,
                'params' => [
                    'form-delete_thumbnail-hidden-csrf' => 'incorrect_csrf',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => null,
                'isFile'          => false,
            ],
            'missing fields - no csrf' => [
                'sqlQueries'           => [
                    "REPLACE INTO blueprints (id, id_author, slug, file_id, title, current_version, created_at, published_at, exposure, thumbnail) VALUES (1, 189, 'slug_public', 'file', 'title_public', 1, utc_timestamp(), utc_timestamp(), 'public', '<script>alert(1)</script>')",
                    "REPLACE INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', utc_timestamp(), utc_timestamp())",
                ],
                'userID'             => 189,
                'params'             => [],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success block__info--side" data-flash-success-for="form-delete_thumbnail">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error block__info--side" data-flash-error-for="form-delete_thumbnail" role="alert">'
                    ]
                ],
                'fileOrDirOnDisk' => null,
                'isFile'          => false,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesDeleteThumbnail
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('dataCasesDeleteThumbnail')]
    public function testBlueprintEditPOSTDeleteThumbnail(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, ?string $fileOrDirOnDisk, bool $isFile): void
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
            $params['form-delete_thumbnail-hidden-csrf'] = $_SESSION['csrf'];
        }

        // thumbnail before
        $thumbnailBefore = static::$db->selectVar('SELECT thumbnail FROM blueprints WHERE id = 1');

        $additionalsFolders = ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias'];

        if ($fileOrDirOnDisk !== null) {
            if ($isFile) {
                \file_put_contents(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk, 'aze');
            } else {
                \mkdir(\dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . $fileOrDirOnDisk);
            }
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/blueprint/slug_public/edit/', $params, [], [], [], [], $additionalsFolders);

        if ($hasRedirection) {
            static::assertSame('/blueprint/slug_public/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        $thumbnailAfter = static::$db->selectVar('SELECT thumbnail FROM blueprints WHERE id = 1');

        if ($isFormSuccess) {
            static::assertNull($thumbnailAfter);
        } else {
            static::assertSame($thumbnailBefore, $thumbnailAfter);
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
