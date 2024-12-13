<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Login;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Crypt\Crypt;
use Rancoud\Crypt\CryptException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class LogoutTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     * @throws CryptException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token);
        SQL;

        $userParams = [
            'id'             => 10,
            'username'       => 'user_10',
            'hash'           => Crypt::hash('wxcvbn'),
            'slug'           => 'user_10',
            'email'          => 'user_10@example.com',
            'grade'          => 'member',
            'remember_token' => 'remember_token_user_10'
        ];
        static::$db->insert($sql, $userParams);
    }

    /**
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        $sql = <<<'SQL'
            UPDATE users SET remember_token = :remember_token WHERE id = 10
        SQL;
        $params = [
            'remember_token' => 'remember_token_user_10'
        ];

        static::$db->update($sql, $params);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function dataCasesLogoutPOST(): array
    {
        return [
            'logout OK' => [
                'params' => [
                    'form-logout-hidden-csrf' => 'csrf_is_replaced',
                ],
                'useCsrfFromSession' => true,
                'hasRedirection'     => true,
                'isFormSuccess'      => true,
            ],
            'csrf incorrect' => [
                'params' => [
                    'form-logout-hidden-csrf' => 'incorrect_csrf',
                ],
                'useCsrfFromSession' => false,
                'hasRedirection'     => false,
                'isFormSuccess'      => false,
            ],
            'missing fields - no fields' => [
                'params'                => [],
                'useCsrfFromSession'    => false,
                'hasRedirection'        => false,
                'isFormSuccess'         => false,
            ],
            'missing fields - no csrf' => [
                'params'                => [],
                'useCsrfFromSession'    => false,
                'hasRedirection'        => false,
                'isFormSuccess'         => false,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesLogoutPOST
     *
     * @param array $params
     * @param bool  $useCsrfFromSession
     * @param bool  $hasRedirection
     * @param bool  $isFormSuccess
     *
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws DatabaseException
     */
    #[DataProvider('dataCasesLogoutPOST')]
    public function testLogoutPOST(array $params, bool $useCsrfFromSession, bool $hasRedirection, bool $isFormSuccess): void
    {
        // generate csrf
        $this->getResponseFromApplication('GET', '/', [], ['remove' => [], 'set' => ['userID' => 10, 'username' => 'user_10', 'grade' => 'member', 'slug' => 'user_10']]);

        // put csrf
        if ($useCsrfFromSession) {
            $params['form-logout-hidden-csrf'] = $_SESSION['csrf'];
        }

        // test response / redirection
        $response = $this->getResponseFromApplication('POST', '/', $params, [], ['remember_token' => 'remember_token_user_10']);

        if ($hasRedirection) {
            if ($isFormSuccess) {
                static::assertSame('/#logout-success', $response->getHeaderLine('Location'));
            } else {
                static::assertSame('/', $response->getHeaderLine('Location'));
            }

            $this->doTestHasResponseWithStatusCode($response, 301);
            $response = $this->getResponseFromApplication('GET', $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 200);
        } else {
            $this->doTestHasResponseWithStatusCode($response, 200);
        }

        if ($isFormSuccess) {
            static::assertNull(static::$db->selectVar('SELECT remember_token FROM users WHERE id = 10'));
            static::assertArrayNotHasKey('userID', $_SESSION);
        } else {
            static::assertNotNull(static::$db->selectVar('SELECT remember_token FROM users WHERE id = 10'));
            static::assertArrayHasKey('userID', $_SESSION);
            static::assertSame(10, $_SESSION['userID']);
            static::assertSame('user_10', $_SESSION['username']);
            static::assertSame('member', $_SESSION['grade']);
            static::assertSame('user_10', $_SESSION['slug']);
        }

        // user logout, cannot logout twice
        if ($isFormSuccess) {
            $response = $this->getResponseFromApplication('POST', '/', $params);
            $this->doTestHasResponseWithStatusCode($response, 200);
        }
    }
}
