<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\Profile\View;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Security\Security;
use Rancoud\Security\SecurityException;
use Rancoud\Session\Session;
use tests\Common;

class ProfileTest extends TestCase
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
            INSERT INTO `users` (`id`, `username`, `password`, `slug`, `email`, `grade`, `created_at`, `avatar`)
                VALUES (:id, :username, :hash, :slug, :email, :grade, UTC_TIMESTAMP(), :avatar);
        SQL;

        $userParams = [
            'id'       => 159,
            'username' => 'user_159',
            'hash'     => null,
            'slug'     => 'user_159',
            'email'    => 'user_159@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 169,
            'username' => 'user_169',
            'hash'     => null,
            'slug'     => 'user_169',
            'email'    => 'user_169@example.com',
            'grade'    => 'member',
            'avatar'   => 'fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 179,
            'username' => 'user_179 <script>alert(1)</script>',
            'hash'     => null,
            'slug'     => 'user_179',
            'email'    => 'user_179@example.com',
            'grade'    => 'member',
            'avatar'   => 'mem\"><script>alert(1)</script>fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);
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
    public static function dataCasesAccess(): array
    {
        return [
            'user not exist' => [
                'sqlQueries'            => [],
                'slug'                  => '4564879864564/',
                'location'              => '/',
                'userID'                => null,
                'contentHead'           => null,
                'contentProfileHTML'    => '',
                'contentBlueprintsHTML' => '',
                'contentPaginationHTML' => '',
            ],
            'public profile - no blueprints - page 2' => [
                'sqlQueries'            => [],
                'slug'                  => 'user_159/?page=2',
                'location'              => '/profile/user_159/',
                'userID'                => null,
                'contentHead'           => null,
                'contentProfileHTML'    => '',
                'contentBlueprintsHTML' => '',
                'contentPaginationHTML' => '',
            ],
            'public profile - visitor' => [
                'sqlQueries'  => [],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'public profile - user connected' => [
                'sqlQueries'  => [],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'author profile' => [
                'sqlQueries'  => [],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCasesProfileInfos(): array
    {
        return [
            'user + avatar' => [
                'sqlQueries'  => [],
                'slug'        => 'user_179/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_179 <script>alert(1)</script> | Page 1 | This is a base title',
                    'description' => 'Profile of user_179 <script>alert(1)</script>'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<img alt="user_179&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt; avatar" class="profile__avatar-container" src="&#x2F;medias&#x2F;avatars&#x2F;mem&#x5C;&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;fromage.jpg"/>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio) VALUES (159, 'user_bio')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio) VALUES (159, 'user_bio <script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website) VALUES (159, 'user_bio\nsecond line', 'website')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio<br />
second line</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + http site' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website) VALUES (159, 'user_bio', 'http://website')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="http&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">http:&#47;&#47;website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + https site' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website) VALUES (159, 'user_bio', 'https://website')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">https:&#47;&#47;website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + facebook' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_facebook) VALUES (159, 'user_bio', 'website', 'facebook')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Facebook" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.facebook.com&#x2F;facebook" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--facebook">
<use href="/sprite/sprite.svg#icon-facebook"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + facebook xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_facebook) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'facebook\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Facebook" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.facebook.com&#x2F;facebook&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--facebook">
<use href="/sprite/sprite.svg#icon-facebook"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + twitter' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_twitter) VALUES (159, 'user_bio', 'website', 'twitter')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Twitter" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;twitter.com&#x2F;twitter" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitter">
<use href="/sprite/sprite.svg#icon-twitter"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + twitter xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_twitter) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'twitter\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Twitter" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;twitter.com&#x2F;twitter&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitter">
<use href="/sprite/sprite.svg#icon-twitter"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + github' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_github) VALUES (159, 'user_bio', 'website', 'github')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="GitHub" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;github.com&#x2F;github" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-github"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + github xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_github) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'github\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="GitHub" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;github.com&#x2F;github&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-github"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + youtube' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_youtube) VALUES (159, 'user_bio', 'website', 'youtube')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Youtube" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.youtube.com&#x2F;channel&#x2F;youtube" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-youtube"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + youtube xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_youtube) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'youtube\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Youtube" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.youtube.com&#x2F;channel&#x2F;youtube&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-youtube"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + twitch' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_twitch) VALUES (159, 'user_bio', 'website', 'twitch')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Twitch" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.twitch.tv&#x2F;twitch" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitch">
<use href="/sprite/sprite.svg#icon-twitch"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + twitch xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_twitch) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'twitch\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Twitch" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;www.twitch.tv&#x2F;twitch&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg profile__network-svg--twitch">
<use href="/sprite/sprite.svg#icon-twitch"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio + site + unreal' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_unreal) VALUES (159, 'user_bio', 'website', 'unreal')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website" rel="noopener noreferrer nofollow" target="_blank">website</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Unreal" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;forums.unrealengine.com&#x2F;u&#x2F;unreal" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-unreal"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + avatar + bio xss + site xss + unreal xss' => [
                'sqlQueries' => [
                    "INSERT INTO users_infos (id_user, bio, link_website, link_unreal) VALUES (159, 'user_bio <script>alert(1)</script>', 'website <script>alert(1)</script>', 'unreal\"><script>alert(1)</script>')"
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio">user_bio &lt;script&gt;alert(1)&lt;&#47;script&gt;</p>
<p class="profile__about-website">Website: <a class="profile__about-website--link" href="https&#x3A;&#x2F;&#x2F;website&#x20;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">website &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
<li class="profile__network">
<a aria-label="Unreal" class="profile__network-link" href="https&#x3A;&#x2F;&#x2F;forums.unrealengine.com&#x2F;u&#x2F;unreal&quot;&gt;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;" rel="noopener noreferrer nofollow" target="_blank">
<svg aria-hidden="true" class="profile__network-svg">
<use href="/sprite/sprite.svg#icon-unreal"></use>
</svg>
</a>
</li>
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases1PrivateBlueprint(): array
    {
        return [
            'user + 1 blueprint private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">1</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 1 blueprint private - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint private - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint private - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'private')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">1</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases1UnlistedBlueprint(): array
    {
        return [
            'user + 1 blueprint unlisted - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint unlisted - created but not published - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint unlisted - created but not published - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">1</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 1 blueprint unlisted - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint unlisted - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">0</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'user + 1 blueprint unlisted - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 0, 1)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`) VALUES (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'unlisted')",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">1</span> blueprint                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases10Public10PrivateBlueprints(): array
    {
        return [
            'user + 5 blueprints public + 3 blueprints private - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 5, 8)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">5</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">1 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">8 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">10 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">14 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">17 days ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 5 blueprints public + 3 blueprints private - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 5, 8)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">5</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">1 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">8 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">10 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">14 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">17 days ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 5 blueprints public + 3 blueprints private - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 5, 8)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">8</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">1 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_13&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_13&#x2F;">title_13</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">4 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_17&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_17&#x2F;">title_17</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">6 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">8 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">10 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">14 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">17 days ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_7&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_7&#x2F;">title_7</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">18 days ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases20Public10PrivateBlueprintsPage1(): array
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new DateTime('now', new DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            'user + 20 blueprints public + 10 blueprints private - page 1 - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">20</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-1 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-8 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-10 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-14 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-17 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">title_21</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-21 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">title_23</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-23 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">title_25</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-25 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">title_27</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-27 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">title_29</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-29 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">title_31</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-31 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">title_33</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-33 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">title_35</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-35 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">title_37</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-37 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">title_39</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-39 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 20 blueprints public + 10 blueprints private - page 1 - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">20</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-1 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-8 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-10 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-14 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-17 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">title_21</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-21 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">title_23</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-23 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">title_25</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-25 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">title_27</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-27 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">title_29</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-29 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">title_31</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-31 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">title_33</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-33 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">title_35</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-35 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">title_37</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-37 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">title_39</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-39 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 20 blueprints public + 10 blueprints private - page 1 - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 1 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">30</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_15&#x2F;">title_15</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-1 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_13&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_13&#x2F;">title_13</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-4 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_17&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_17&#x2F;">title_17</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-6 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_5&#x2F;">title_5</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-8 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_2&#x2F;">title_2</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-10 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_9&#x2F;">title_9</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-14 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_18&#x2F;">title_18</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-17 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_7&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_7&#x2F;">title_7</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-18 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_21&#x2F;">title_21</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-21 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_22&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_22&#x2F;">title_22</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-22 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_23&#x2F;">title_23</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-23 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_24&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_24&#x2F;">title_24</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-24 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_25&#x2F;">title_25</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-25 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_26&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_26&#x2F;">title_26</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-26 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_27&#x2F;">title_27</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-27 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
        ];
    }

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases20Public10PrivateBlueprintsPage2(): array
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new DateTime('now', new DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            'user + 20 blueprints public + 10 blueprints private - page 2 - (visitor profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/?page=2',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 2 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">20</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">title_41</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-41 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">title_42</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-42 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">title_43</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-43 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">title_44</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-44 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">title_45</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-45 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 20 blueprints public + 10 blueprints private - page 2 - (public profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/?page=2',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 2 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">20</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">title_41</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-41 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">title_42</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-42 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">title_43</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-43 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">title_44</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-44 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">title_45</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-45 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
            'user + 20 blueprints public + 10 blueprints private - page 2 - (author profile)' => [
                'sqlQueries' => [
                    'INSERT INTO users_infos (`id_user`, `count_public_blueprint`, `count_private_blueprint`) VALUES (159, 20, 30)',
                    "INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public'),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public')
                    ",
                ],
                'slug'        => 'user_159/?page=2',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Profile of user_159 | Page 2 | This is a base title',
                    'description' => 'Profile of user_159'
                ],
                'contentProfileHTML' => <<<HTML
<div class="block__container block__container--first">
<div class="block__element">
<div class="profile">
<div class="profile__avatar-area">
<div class="profile__avatar-container profile__avatar-container--background">
<svg class="profile__avatar-svg">
<use href="/sprite/sprite.svg#avatar"></use>
</svg>
</div>
</div>
<div class="profile__name-area">
<h2 class="profile__name">user_159</h2>
<a class="block__link block__link--edit-profile" href="/profile/user_159/edit/">Edit profile</a>
</div>
<div class="profile__stats-area">
<ul class="profile__stats">
<li class="profile__stat">
<span class="profile__stat-number">30</span> blueprints                            </li>
<li class="profile__stat profile__stat--last">
<span class="profile__stat-number">0</span> comment                            </li>
</ul>
</div>
<div class="profile__hr-area">
<hr class="profile__hr"/>
</div>
<div class="profile__about-area">
<p class="profile__about-bio"></p>
</div>
<div class="profile__networks-area">
<ul class="profile__networks">
</ul>
</div>
</div>
</div>
</div>
HTML,
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--last block__container--white-grey block__container--shadow-top">
<div class="block__element">
<h2 class="block__title">Owned <span class="block__title--emphasis">blueprints</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<ul class="list">
<li class="list__row list__row--header">
<div class="list__col list__col--header list__col--first">Image</div>
<div class="list__col list__col--header">Type</div>
<div class="list__col list__col--header">UE Version</div>
<div class="list__col list__col--header">Title</div>
<div class="list__col list__col--header">Author</div>
<div class="list__col list__col--header">Date</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_28&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_28&#x2F;">title_28</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-28 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_29&#x2F;">title_29</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-29 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_31&#x2F;">title_31</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-31 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_32&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_32&#x2F;">title_32</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-32 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_33&#x2F;">title_33</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-33 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_35&#x2F;">title_35</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-35 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_36&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_36&#x2F;">title_36</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-36 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_37&#x2F;">title_37</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-37 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_38&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_38&#x2F;">title_38</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-38 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_39&#x2F;">title_39</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-39 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_41&#x2F;">title_41</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-41 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_42&#x2F;">title_42</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-42 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_43&#x2F;">title_43</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-43 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_44&#x2F;">title_44</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-44 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_45&#x2F;">title_45</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-45 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<HTML
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;profile&#x2F;user_159&#x2F;&#x3F;page&#x3D;1" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
</nav>            </div>
HTML,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesAccess
     * @dataProvider dataCasesProfileInfos
     * @dataProvider dataCases1PrivateBlueprint
     * @dataProvider dataCases1UnlistedBlueprint
     * @dataProvider dataCases10Public10PrivateBlueprints
     * @dataProvider dataCases20Public10PrivateBlueprintsPage1
     * @dataProvider dataCases20Public10PrivateBlueprintsPage2
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCasesAccess')]
    #[DataProvider('dataCasesProfileInfos')]
    #[DataProvider('dataCases1PrivateBlueprint')]
    #[DataProvider('dataCases1UnlistedBlueprint')]
    #[DataProvider('dataCases10Public10PrivateBlueprints')]
    #[DataProvider('dataCases20Public10PrivateBlueprintsPage1')]
    #[DataProvider('dataCases20Public10PrivateBlueprintsPage2')]
    public function testProfileGET(array $sqlQueries, string $slug, ?string $location, ?int $userID, ?array $contentHead, string $contentProfileHTML, string $contentBlueprintsHTML, string $contentPaginationHTML): void
    {
        static::setDatabase();
        static::$db->truncateTables('blueprints');
        static::$db->truncateTables('users_infos');
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        $sessionValues = [
            'set'    => [],
            'remove' => ['userID', 'username', 'grade', 'slug']
        ];

        if ($userID !== null) {
            $users = [
                159 => ['userID' => $userID, 'username' => 'user_159', 'grade' => 'member', 'slug' => 'user_159'],
                179 => ['userID' => $userID, 'username' => 'user_179 <script>alert(1)</script>', 'grade' => 'member', 'slug' => 'user_179'],
            ];
            $sessionValues = [
                'set'    => $users[$userID],
                'remove' => []
            ];
        }

        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        $parsedUrl = \parse_url($slug);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            \parse_str($parsedUrl['query'], $queryParams);
        }

        $response = $this->getResponseFromApplication('GET', '/profile/' . $slug, [], [], [], $queryParams);
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

        $this->doTestHtmlMain($response, $contentProfileHTML);
        $this->doTestHtmlMain($response, $contentBlueprintsHTML);
        $this->doTestHtmlMain($response, $contentPaginationHTML);
    }
}
