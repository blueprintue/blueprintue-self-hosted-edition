<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Profile\Edit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class ProfileEditPOSTGenerateAPIKeyTest extends TestCase
{
    use Common;

    /**
     * @throws \Rancoud\Crypt\CryptException
     * @throws DatabaseException
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

    public static function provideProfileEditPOSTGenerateApiKeyDataCases(): iterable
    {
        yield 'edit OK' => [
            'sqlQueries' => [],
            'userID'     => 189,
            'params'     => [
                'form-generate_api_key-hidden-csrf' => 'csrf_is_replaced',
            ],
            'useCsrfFromSession' => true,
            'hasRedirection'     => true,
            'isFormSuccess'      => true,
            'flashMessages'      => [
                'success' => [
                    'has'     => true,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-generate_api_key">Your api key is now generated</div>'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-generate_api_key" role="alert">'
                ]
            ],
        ];

        yield 'csrf incorrect' => [
            'sqlQueries' => [],
            'userID'     => 189,
            'params'     => [
                'form-generate_api_key-hidden-csrf' => 'incorrect_csrf',
            ],
            'useCsrfFromSession' => false,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-generate_api_key">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-generate_api_key" role="alert">'
                ]
            ],
        ];

        yield 'missing fields - no csrf' => [
            'sqlQueries'         => [],
            'userID'             => 189,
            'params'             => [],
            'useCsrfFromSession' => false,
            'hasRedirection'     => false,
            'isFormSuccess'      => false,
            'flashMessages'      => [
                'success' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--success" data-flash-success-for="form-generate_api_key">'
                ],
                'error' => [
                    'has'     => false,
                    'message' => '<div class="block__info block__info--error" data-flash-error-for="form-generate_api_key" role="alert">'
                ]
            ],
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideProfileEditPOSTGenerateApiKeyDataCases')]
    public function testProfileEditPOSTGenerateApiKey(array $sqlQueries, int $userID, array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess, array $flashMessages): void
    {
        static::setDatabase();
        static::$db->truncateTables('users_api');

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
            $params['form-generate_api_key-hidden-csrf'] = $_SESSION['csrf'];
        }

        // user before
        $userApiBefore = static::$db->selectRow('SELECT * FROM users_api WHERE id_user = ' . $userID);

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/profile/user_' . $userID . '/edit/', $params);

        if ($hasRedirection) {
            static::assertSame('/profile/user_' . $userID . '/edit/', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        // user after
        $userApiAfter = static::$db->selectRow('SELECT * FROM users_api WHERE id_user = ' . $userID);

        if ($isFormSuccess) {
            static::assertNotSame($userApiBefore, $userApiAfter);
        } else {
            static::assertSame($userApiBefore, $userApiAfter);
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

        $apiKey = $userApiAfter['api_key'] ?? '';
        $this->doTestHtmlBody($response, <<<HTML
<div class="form__element">
<label class="form__label" for="form-generate_api_key-input-current_api_key" id="form-generate_api_key-label-current_api_key">Current API Key</label>
<input aria-labelledby="form-generate_api_key-label-current_api_key" class="form__input form__input--disabled" disabled id="form-generate_api_key-input-current_api_key" name="form-generate_api_key-input-current_api_key" type="text" value="{$apiKey}"/>
</div>
HTML);
    }
}
