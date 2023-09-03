<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Register;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Session\Session;
use tests\Common;

class ConfirmAccountTest extends TestCase
{
    use Common;

    /**
     * @throws DatabaseException
     */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();

        // user generation
        $sql = <<<'SQL'
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `confirmed_token`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :confirmed_token);
        SQL;

        $userParams = [
            'id'              => 44,
            'username'        => 'user_44',
            'hash'            => null,
            'slug'            => 'user_44',
            'email'           => 'user_44@example.com',
            'grade'           => 'member',
            'confirmed_token' => 'I8jJaUjUlIRUHfWZeUf4Ntmkj0Uvo0Ij7b9vMr9bSZpSBGlIlekNK5N5cqWsdJVb98IoRu3nvxoIesT6pKW65S25vagl1bSqyuDSGZ4GTKFBmuOwrptiF7ygnY6HOnEZPiRQ2FycFn84tNXkDgVDs68keZkbqu5D9KlVs4ghlIbpKcDlpQWo83ZgYGDwlv4exw6FSxKODPEuv2lNgMLqZKvPXZHKki4fHstdGdnQU6BnGaBzToB1oebnLAHPZ2R'
        ];
        static::$db->insert($sql, $userParams);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public function dataCasesConfirmAccount(): array
    {
        return [
            'user is logged - redirect' => [
                'slug'                 => '/confirm-account/',
                'location'             => '/',
                'user_id'              => 4,
                'content_head'         => [],
                'has_redirection'      => true,
                'is_confirmed_account' => null,
                'text'                 => null,
                'js_redirect'          => null,
            ],
            'no token - welcome to blueprintUE.com' => [
                'slug'         => '/confirm-account/',
                'location'     => null,
                'user_id'      => null,
                'content_head' => [
                    'title'       => 'Confirm Account | This is a base title',
                    'description' => 'Confirm Account',
                    'url'         => '/confirm-account/',
                ],
                'has_redirection'      => false,
                'is_confirmed_account' => null,
                'text'                 => '<p>Welcome to this_site_name</p><p>Before login to your account you need to confirm it.<br />You will receive an email with a link for the confirmation.</p>',
                'js_redirect'          => "<script>setTimeout(function(){window.location.href = '/#popin-login'}, 5000);</script>",
            ],
            'token invalid - Your account is maybe already confirmed' => [
                'slug'         => '/confirm-account/?confirmed_token=invalid',
                'location'     => null,
                'user_id'      => null,
                'content_head' => [
                    'title'       => 'Confirm Account | This is a base title',
                    'description' => 'Confirm Account',
                    'url'         => '/confirm-account/',
                ],
                'has_redirection'      => false,
                'is_confirmed_account' => false,
                'text'                 => '<p>Your account is maybe already confirmed or your confirmed token is invalid.<br /><a class="blog__link" href="/#popin-login">Go back to homepage for login.</a></p>',
                'js_redirect'          => "<script>setTimeout(function(){window.location.href = '/#popin-login'}, 5000);</script>",
            ],
            'token valid - Your account is now confirmed!' => [
                'slug'         => '/confirm-account/?confirmed_token=I8jJaUjUlIRUHfWZeUf4Ntmkj0Uvo0Ij7b9vMr9bSZpSBGlIlekNK5N5cqWsdJVb98IoRu3nvxoIesT6pKW65S25vagl1bSqyuDSGZ4GTKFBmuOwrptiF7ygnY6HOnEZPiRQ2FycFn84tNXkDgVDs68keZkbqu5D9KlVs4ghlIbpKcDlpQWo83ZgYGDwlv4exw6FSxKODPEuv2lNgMLqZKvPXZHKki4fHstdGdnQU6BnGaBzToB1oebnLAHPZ2R',
                'location'     => null,
                'user_id'      => null,
                'content_head' => [
                    'title'       => 'Confirm Account | This is a base title',
                    'description' => 'Confirm Account',
                    'url'         => '/confirm-account/',
                ],
                'has_redirection'      => false,
                'is_confirmed_account' => true,
                'text'                 => '<p>Your account is now confirmed!<br />You can now log to your account.<br /><a class="blog__link" href="/#popin-login">Go back to homepage for login.</a></p>',
                'js_redirect'          => "<script>setTimeout(function(){window.location.href = '/#popin-login'}, 5000);</script>",
            ],
        ];
    }

    /**
     * @dataProvider dataCasesConfirmAccount
     *
     * @param string      $slug
     * @param string|null $location
     * @param int|null    $userID
     * @param array|null  $contentHead
     * @param bool        $hasRedirection
     * @param bool|null   $isConfirmedAccount
     * @param string|null $text
     * @param string|null $jsRedirect
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws \Rancoud\Security\SecurityException
     */
    public function testConfirmAccountPOST(string $slug, ?string $location, ?int $userID, ?array $contentHead, bool $hasRedirection, ?bool $isConfirmedAccount, ?string $text, ?string $jsRedirect): void
    {
        $sessionValues = [
            'set'    => [],
            'remove' => ['userID', 'username', 'grade', 'slug']
        ];

        if ($userID !== null) {
            $sessionValues = [
                'set'    => ['userID' => $userID],
                'remove' => []
            ];
        }

        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        $parsedUrl = \parse_url($slug);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            \parse_str($parsedUrl['query'], $queryParams);
        }

        $userBefore = static::$db->selectRow('SELECT * FROM users WHERE id = 44');

        // test response / redirection
        $response = $this->getResponseFromApplication('GET', $slug, [], [], [], $queryParams);

        if ($hasRedirection) {
            static::assertSame($location, $response->getHeaderLine('Location'));
            $this->doTestHasResponseWithStatusCode($response, 301);

            return;
        }

        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($contentHead['title']),
            'description' => Security::escAttr($contentHead['description'])
        ]);
        $this->doTestNavBarIsLogoOnly($response);

        // test text on page
        $this->doTestHtmlMain($response, $text);

        if ($jsRedirect !== null) {
            $this->doTestHtmlMain($response, $text);
        }

        $userAfter = static::$db->selectRow('SELECT * FROM users WHERE id = 44');
        if ($isConfirmedAccount) {
            static::assertNull($userAfter['confirmed_token']);
            static::assertNotNull($userAfter['confirmed_at']);

            $response = $this->getResponseFromApplication('GET', $slug, [], [], [], $queryParams);
            $this->doTestHtmlMain($response, '<p>Your account is maybe already confirmed or your confirmed token is invalid.<br /><a class="blog__link" href="/#popin-login">Go back to homepage for login.</a></p>');
        } else {
            static::assertSame($userBefore, $userAfter);
        }
    }
}
