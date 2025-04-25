<?php

/* @noinspection PhpTooManyParametersInspection */

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

/** @internal */
class LoginWithRememberTest extends TestCase
{
    use Common;

    /**
     * @throws CryptException
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `remember_token`, `confirmed_sent_at`, `confirmed_at`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :remember_token, :confirmed_sent_at, :confirmed_at);
        SQL;

        $userParams = [
            'id'                => 5,
            'username'          => 'user_5',
            'hash'              => Crypt::hash('qsdfghjklm'),
            'slug'              => 'user_5',
            'email'             => 'user_5@example.com',
            'grade'             => 'member',
            'remember_token'    => 'XDYtGT691XiPWiUZSUGCt21zWF7svbnEbmqjrxDmdP1Wqs3fkIEuSu98iwSJcddVH8shXtaznY5UNbZlF8Qbyp6m4vxbKlY7GWBLL8V9wAPd4xr0yHfnlZokaPMKfJY3nQkrgHq3xxUkARPe8NhxgaHPpWw8F99rtSn9Tpalf0QiKIwsOG9T0S7ssNUtOENB1lPal2jW4kuqdnAS7Jvy19bYeJasy7koLOyrCo6aqt6UfuSgLI6ClhNVsAtKkm0',
            'confirmed_sent_at' => \gmdate('Y-m-d H:i:s'),
            'confirmed_at'      => \gmdate('Y-m-d H:i:s'),
        ];
        static::$db->insert($sql, $userParams);
    }

    /** @throws DatabaseException */
    protected function setUp(): void
    {
        static::$db->update('UPDATE users SET last_login_at = NULL');
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function provideLoginWithRememberDataCases(): iterable
    {
        return [
            'error - no login because remember invalid' => [
                'rememberToken'   => 'poi',
                'bodyContains'    => ['<a class="nav__user-button nav__user-button--left" href="#popin-login">Log in</a>'],
                'bodyNotContains' => ['<button class="nav__user-button-logout" type="submit">Logout</button>'],
            ],
            'success login with remember' => [
                'rememberToken'   => 'XDYtGT691XiPWiUZSUGCt21zWF7svbnEbmqjrxDmdP1Wqs3fkIEuSu98iwSJcddVH8shXtaznY5UNbZlF8Qbyp6m4vxbKlY7GWBLL8V9wAPd4xr0yHfnlZokaPMKfJY3nQkrgHq3xxUkARPe8NhxgaHPpWw8F99rtSn9Tpalf0QiKIwsOG9T0S7ssNUtOENB1lPal2jW4kuqdnAS7Jvy19bYeJasy7koLOyrCo6aqt6UfuSgLI6ClhNVsAtKkm0',
                'bodyContains'    => ['<button class="nav__user-button-logout" type="submit">Logout</button>'],
                'bodyNotContains' => ['<a class="nav__user-button nav__user-button--left" href="#popin-login">Log in</a>'],
            ]
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideLoginWithRememberDataCases')]
    public function testLoginGETLoginWithRemember(string $rememberToken, array $bodyContains, array $bodyNotContains): void
    {
        $validRememberToken = 'XDYtGT691XiPWiUZSUGCt21zWF7svbnEbmqjrxDmdP1Wqs3fkIEuSu98iwSJcddVH8shXtaznY5UNbZlF8Qbyp6m4vxbKlY7GWBLL8V9wAPd4xr0yHfnlZokaPMKfJY3nQkrgHq3xxUkARPe8NhxgaHPpWw8F99rtSn9Tpalf0QiKIwsOG9T0S7ssNUtOENB1lPal2jW4kuqdnAS7Jvy19bYeJasy7koLOyrCo6aqt6UfuSgLI6ClhNVsAtKkm0';

        $response = $this->getResponseFromApplication('GET', '/', [], [], ['remember_token' => $rememberToken]);
        if ($validRememberToken !== $rememberToken) {
            $this->doTestHasResponseWithStatusCode($response, 200);
            static::assertSame($validRememberToken, static::$db->selectVar('SELECT remember_token FROM users WHERE id = 5'));

            static::assertNull(static::$db->selectVar('SELECT last_login_at FROM users WHERE id = 5'));
        } else {
            $newRememberToken = static::$db->selectVar('SELECT remember_token FROM users WHERE id = 5');
            static::assertNotSame($validRememberToken, $newRememberToken);
            static::assertNotNull($newRememberToken);
            $this->doTestHasResponseWithStatusCode($response, 301);

            $response = $this->getResponseFromApplication('GET', '/', [], [], ['remember_token' => $rememberToken]);
            $this->doTestHasResponseWithStatusCode($response, 200);

            static::assertNotNull(static::$db->selectVar('SELECT last_login_at FROM users WHERE id = 5'));
        }

        foreach ($bodyContains as $inBody) {
            static::assertStringContainsString($inBody, (string) $response->getBody());
        }

        foreach ($bodyNotContains as $inNotBody) {
            static::assertStringNotContainsString($inNotBody, (string) $response->getBody());
        }
    }
}
