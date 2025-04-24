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
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintEditPOSTAddVersionTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws \Rancoud\Crypt\CryptException
     */
    public static function setUpBeforeClass(): void
    {
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

    public static function dataCasesAddVersion(): array
    {
        return [
            'update OK - add version' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "INSERT INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                    "INSERT INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 2,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">The new version has been published</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'update OK - add version with max id + 1' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 2, utc_timestamp(), utc_timestamp(), 'private')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), null)",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (901, 80, 2, 'Second commit', utc_timestamp(), null)",
                ],
                'userID'        => 189,
                'countVersions' => 3,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
                'flashMessages'      => [
                    'success' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">The new version has been published</div>'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'update KO - add version failed because blueprint versions keys integrity failed' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 99999, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'userID'        => 189,
                'countVersions' => 0,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error, could not add version blueprint (#300)</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => ['blueprint', 'reason'],
                'fieldsLabelError' => [],
            ],
            'csrf incorrect' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'incorrect_csrf',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no csrf' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no blueprint' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'missing fields - no reason' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error, missing fields</div>'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'empty fields - blueprint' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => ' ',
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error(s) on blueprint</div>'
                    ]
                ],
                'fieldsHasError'   => ['blueprint'],
                'fieldsHasValue'   => ['blueprint', 'reason'],
                'fieldsLabelError' => [
                    'blueprint' => 'Blueprint is required'
                ],
            ],
            'empty fields - reason' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => ' ',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error(s) on reason</div>'
                    ]
                ],
                'fieldsHasError'   => ['reason'],
                'fieldsHasValue'   => ['blueprint', 'reason'],
                'fieldsLabelError' => [
                    'reason' => 'Reason is required'
                ],
            ],
            'invalid fields - blueprint invalid' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => '<script>alert(1)</script></textarea><script>alert(1)</script>',
                    'form-add_version-textarea-reason'    => '<script>alert(1)</script></textarea><script>alert(1)</script>',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => true,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">Error(s) on blueprint</div>'
                    ]
                ],
                'fieldsHasError'   => ['blueprint'],
                'fieldsHasValue'   => ['blueprint', 'reason'],
                'fieldsLabelError' => [
                    'blueprint' => 'Blueprint is invalid'
                ],
            ],
            'invalid encoding fields - blueprint' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => \chr(99999999),
                    'form-add_version-textarea-reason'    => 'new version',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
            'invalid encoding fields - reason' => [
                'sqlQueries' => [
                    'TRUNCATE TABLE blueprints',
                    'TRUNCATE TABLE blueprints_version',
                    "REPLACE INTO blueprints (`id`, `id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (80, 189, 'slug_1', 'f1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public')",
                    "REPLACE INTO blueprints_version (`id`, `id_blueprint`, `version`, `reason`, `created_at`, `published_at`) VALUES (900, 80, 1, 'Initial', utc_timestamp(), utc_timestamp())",
                ],
                'userID'        => 189,
                'countVersions' => 1,
                'params'        => [
                    'form-add_version-hidden-csrf'        => 'csrf_is_replaced',
                    'form-add_version-textarea-blueprint' => 'Begin object 1234',
                    'form-add_version-textarea-reason'    => \chr(99999999),
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
                'flashMessages'      => [
                    'success' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--success" data-flash-success-for="form-add_version">'
                    ],
                    'error' => [
                        'has'     => false,
                        'message' => '<div class="block__info block__info--error" data-flash-error-for="form-add_version" role="alert">'
                    ]
                ],
                'fieldsHasError'   => [],
                'fieldsHasValue'   => [],
                'fieldsLabelError' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesAddVersion
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesAddVersion')]
    public function testBlueprintEditPOSTAddVersion(array $sqlQueries, int $userID, int $countVersions, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages, array $fieldsHasError, array $fieldsHasValue, array $fieldsLabelError): void
    {
        static::setDatabase();
        static::cleanFiles();

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
            $params['form-add_version-hidden-csrf'] = $_SESSION['csrf'];
        }

        // set files
        $this->createBlueprintFile('f1');

        // database before
        $blueprintBefore = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');
        $blueprintVersionsBefore = static::$db->selectAll('SELECT * FROM blueprints_version WHERE id_blueprint = 80');

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/blueprint/slug_1/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/blueprint/slug_1/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // database after
        $blueprintAfter = static::$db->selectRow('SELECT * FROM blueprints WHERE id = 80');
        $blueprintVersionsAfter = static::$db->selectAll('SELECT * FROM blueprints_version WHERE id_blueprint = 80');

        if ($isFormSuccess) {
            static::assertNotSame($blueprintBefore, $blueprintAfter);
            static::assertNotSame($blueprintVersionsBefore, $blueprintVersionsAfter);

            // blueprint
            switch ($countVersions) {
                case 2:
                    static::assertSame(2, (int) $blueprintAfter['current_version']);

                    // blueprint versions
                    static::assertSame(1, (int) $blueprintVersionsAfter[0]['version']);
                    static::assertSame(2, (int) $blueprintVersionsAfter[1]['version']);
                    break;
                case 3:
                    static::assertSame(3, (int) $blueprintAfter['current_version']);

                    // blueprint versions
                    static::assertSame(1, (int) $blueprintVersionsAfter[0]['version']);
                    static::assertSame(2, (int) $blueprintVersionsAfter[1]['version']);
                    static::assertSame(3, (int) $blueprintVersionsAfter[2]['version']);
                    break;
                default:
                    static::fail($countVersions . ' not managed');
            }

            // file check - must have new file present + content blueprint inside
            $caracters = \mb_str_split($blueprintAfter['file_id']);
            $subfolder = '';
            foreach ($caracters as $c) {
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }
            $subfolder = \mb_strtolower($subfolder);

            $storageFolder = \dirname(__DIR__, 3) . \DIRECTORY_SEPARATOR . 'storage_test' . \DIRECTORY_SEPARATOR;
            $fullpath = $storageFolder . $subfolder . $blueprintAfter['file_id'] . '-' . $blueprintAfter['current_version'] . '.txt';
            static::assertFileExists($fullpath);

            static::assertSame($params['form-add_version-textarea-blueprint'], \file_get_contents($fullpath));
        } else {
            static::assertSame($blueprintBefore, $blueprintAfter);
            static::assertSame($blueprintVersionsBefore, $blueprintVersionsAfter);

            static::assertCount($countVersions, $blueprintVersionsAfter);
        }

        $this->doTestHtmlForm($response, '#form-add_version', '<h2 class="block__title block__title--form-first">Add new version <span class="block__title--emphasis">blueprint</span> — Current version: ' . Security::escHTML($blueprintAfter['current_version']) . '</h2>');

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

        // test fields HTML
        $fields = ['blueprint', 'reason'];
        foreach ($fields as $field) {
            $hasError = \in_array($field, $fieldsHasError, true);
            $hasValue = \in_array($field, $fieldsHasValue, true);
            $labelError = $fieldsLabelError[$field] ?? '';

            if ($field === 'blueprint') {
                $value = $hasValue ? Helper::trim($params['form-add_version-textarea-blueprint']) : '';
                $this->doTestHtmlForm($response, '#form-add_version', $this->getHTMLFieldBlueprint($value, $hasError, $labelError));
            }

            if ($field === 'reason') {
                $value = $hasValue ? Helper::trim($params['form-add_version-textarea-reason']) : '';
                $this->doTestHtmlForm($response, '#form-add_version', $this->getHTMLFieldReason($value, $hasError, $labelError));
            }
        }
    }

    /**
     * @throws SecurityException
     */
    protected function getHTMLFieldBlueprint(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-add_version-textarea-blueprint" id="form-add_version-label-blueprint">New version <span class="form__label--info">(required)</span></label>
<div class="form__container form__container--blueprint form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-add_version-label-blueprint form-add_version-label-blueprint-error" aria-required="true" class="form__input form__input--blueprint form__input--invisible form__input--textarea form__input--error" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-blueprint" name="form-add_version-textarea-blueprint">$v</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-add_version-textarea-blueprint" id="form-add_version-label-blueprint-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-add_version-textarea-blueprint" id="form-add_version-label-blueprint">New version <span class="form__label--info">(required)</span></label>
<div class="form__container form__container--blueprint form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-add_version-label-blueprint" aria-required="true" class="form__input form__input--blueprint form__input--invisible form__input--textarea" data-form-error-required="Blueprint is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-blueprint" name="form-add_version-textarea-blueprint">$v</textarea>
<span class="form__feedback"></span>
</div>
</div>
HTML;
    }

    /**
     * @throws SecurityException
     */
    protected function getHTMLFieldReason(string $value, bool $hasError, string $labelError): string
    {
        $v = Security::escHTML($value);

        if ($hasError) {
            return <<<HTML
<div class="form__element">
<label class="form__label" for="form-add_version-textarea-reason" id="form-add_version-label-reason">Reason <span class="form__label--info">(required)</span></label>
<div class="form__container form__container--textarea form__container--error">
<textarea aria-invalid="false" aria-labelledby="form-add_version-label-reason form-add_version-label-reason-error" aria-required="true" class="form__input form__input--invisible form__input--textarea form__input--error" data-form-error-required="Reason is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-reason" name="form-add_version-textarea-reason">$v</textarea>
<span class="form__feedback form__feedback--error"></span>
</div>
<label class="form__label form__label--error" for="form-add_version-textarea-reason" id="form-add_version-label-reason-error">$labelError</label>
</div>
HTML;
        }

        return <<<HTML
<div class="form__element">
<label class="form__label" for="form-add_version-textarea-reason" id="form-add_version-label-reason">Reason <span class="form__label--info">(required)</span></label>
<div class="form__container form__container--textarea">
<textarea aria-invalid="false" aria-labelledby="form-add_version-label-reason" aria-required="true" class="form__input form__input--invisible form__input--textarea" data-form-error-required="Reason is required" data-form-has-container data-form-rules="required" id="form-add_version-textarea-reason" name="form-add_version-textarea-reason">$v</textarea>
<span class="form__feedback"></span>
</div>
</div>
HTML;
    }
}
