<?php

declare(strict_types=1);

namespace tests\www\Profile\Edit;

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

/** @internal */
class ProfileEditGETTest extends TestCase
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

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function provideAccessDataCases(): iterable
    {
        yield 'redirect - user not exist' => [
            'slug'        => '4564879864564',
            'location'    => '/profile/4564879864564/',
            'userID'      => null,
            'contentHead' => null,
        ];

        yield 'redirect - visitor' => [
            'slug'        => 'user_189',
            'location'    => '/profile/user_189/',
            'userID'      => null,
            'contentHead' => null,
        ];

        yield 'redirect - user connected' => [
            'slug'        => 'user_189',
            'location'    => '/profile/user_189/',
            'userID'      => 199,
            'contentHead' => null,
        ];

        yield 'redirect - anonymous user connected (not possible)' => [
            'slug'        => 'anonymous',
            'location'    => '/profile/anonymous/',
            'userID'      => 2,
            'contentHead' => null,
        ];

        yield 'redirect - user connected but not exists in database (not possible)' => [
            'slug'        => 'inexistent_user',
            'location'    => '/profile/inexistent_user/',
            'userID'      => 50,
            'contentHead' => null,
        ];

        yield 'OK - user is author' => [
            'slug'        => 'user_189',
            'location'    => null,
            'userID'      => 189,
            'contentHead' => [
                'title'       => 'Edit profile of user_189 | This is a base title',
                'description' => 'Edit profile of user_189'
            ],
        ];

        yield 'OK - user is author (xss)' => [
            'slug'        => 'user_199',
            'location'    => null,
            'userID'      => 199,
            'contentHead' => [
                'title'       => 'Edit profile of user_199 | This is a base title',
                'description' => 'Edit profile of user_199'
            ],
        ];
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('provideAccessDataCases')]
    public function testProfileEditGET(string $slug, ?string $location, ?int $userID, ?array $contentHead): void
    {
        $sessionValues = [
            'set'    => [],
            'remove' => ['userID', 'username', 'grade', 'slug']
        ];

        if ($userID !== null) {
            $users = [
                2   => ['userID' => 2, 'username' => 'anonymous', 'grade' => 'member', 'slug' => 'anonymous'],
                50  => ['userID' => 50, 'username' => 'inexistent_user', 'grade' => 'member', 'slug' => 'inexistent_user'],
                189 => ['userID' => $userID, 'username' => 'user_189', 'grade' => 'member', 'slug' => 'user_189'],
                199 => ['userID' => $userID, 'username' => 'user_199 <script>alert(1)</script>', 'grade' => 'member', 'slug' => 'user_199'],
            ];
            $sessionValues = [
                'set'    => $users[$userID],
                'remove' => []
            ];
        }

        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        $response = $this->getResponseFromApplication('GET', '/profile/' . $slug . '/edit/');
        if ($location !== null) {
            $this->doTestHasResponseWithStatusCode($response, 301);
            static::assertSame($location, $response->getHeaderLine('Location'));

            return;
        }

        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($contentHead['title']),
            'description' => Security::escAttr($contentHead['description'])
        ]);
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);

        // verif avatar
        if ($userID === 189) {
            $this->doTestHtmlBody($response, <<<'HTML'
<div class="profile__avatar-container" id="current-avatar">
<img alt="avatar author" class="profile__avatar-container profile__avatar-container--hidden" id="upload-current-avatar"/>
<div class="profile__avatar-container profile__avatar-container--background" id="upload-fallback-avatar">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
HTML);
        } else {
            $this->doTestHtmlBody($response, <<<'HTML'
<div class="profile__avatar-container" id="current-avatar">
<img alt="avatar author" class="profile__avatar-container" id="upload-current-avatar" src="&#x2F;medias&#x2F;avatars&#x2F;mem&#x5C;&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;fromage.jpg"/>
</div>
HTML);
        }
    }
}
