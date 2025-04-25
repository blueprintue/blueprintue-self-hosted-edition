<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Lists;

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

/** @internal */
class TagListTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
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

        static::$db->insert("INSERT INTO tags (`id`, `name`, `slug`) VALUES (24, 'yo', 'lo'), (34, 'pa<script>alert(1);</script>', 'xss')");
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
    public static function provideTagAccessDataCases(): iterable
    {
        return [
            'tag not found' => [
                'sqlQueries'  => [],
                'slug'        => '/tag/not_found/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag not_found | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as not_found'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">not_found</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'tag found' => [
                'sqlQueries'  => [],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            'tag xss found' => [
                'sqlQueries'  => [],
                'slug'        => '/tag/xss/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag pa<script>alert(1);</script> | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as pa<script>alert(1);</script>'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">pa&lt;script&gt;alert(1);&lt;&#47;script&gt;</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
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
    public static function provide3PublicUnlistedPrivateBlueprintTagNotGoodDataCases(): iterable
    {
        return [
            '3 blueprints public/unlisted/private - created but not published - (visitor profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - created but not published - (public profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - created but not published - (author profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (visitor profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (public profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (author profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (visitor profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (public profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (author profile) - tag not good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '1'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '1'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
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
    public static function provide3PublicUnlistedPrivateBlueprintTagGoodDataCases(): iterable
    {
        return [
            '3 blueprints public/unlisted/private - created but not published - (visitor profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - created but not published - (public profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - created but not published - (author profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (visitor profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (public profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - deleted - (author profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`, `deleted_at`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24', utc_timestamp()),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24', utc_timestamp()),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24', utc_timestamp())
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
<hr class="block__hr block__hr--small"/>
</div>
<div class="block__element">
<p>No blueprints for the moment</p>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (visitor profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (public profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
</ul>
HTML,
            ],
            '3 blueprints public/unlisted/private - (author profile) - tag good' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`) VALUES
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', '24'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', '24'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', '24')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">title_3</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_159&#x2F;">user_159</a></div>
<div class="list__col" data-name="Date">few seconds ago</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
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
    public static function provide30PublicUnlistedPrivateBlueprintTagGoodPage1DataCases(): iterable
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new DateTime('now', new DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            '30 blueprints public/unlisted/private - (visitor profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-2 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">title_3</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-3 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">title_14</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-5 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">title_20</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-7 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">title_6</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-9 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">title_16</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-11 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">title_12</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-12 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">title_10</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-13 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">title_4</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-15 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">title_8</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-16 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">title_11</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-19 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">title_19</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-20 days']}</div>
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
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (public profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-2 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">title_3</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-3 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">title_14</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-5 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">title_20</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-7 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">title_6</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-9 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">title_16</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-11 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">title_12</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-12 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">title_10</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-13 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">title_4</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-15 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">title_8</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-16 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">title_11</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-19 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">title_19</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-20 days']}</div>
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
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (author profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/1/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 1 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_1&#x2F;">title_1</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-2 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_3&#x2F;">title_3</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-3 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_14&#x2F;">title_14</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-5 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_20&#x2F;">title_20</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-7 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_6&#x2F;">title_6</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-9 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_16&#x2F;">title_16</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-11 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_12&#x2F;">title_12</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_169&#x2F;">user_169</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-12 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_10&#x2F;">title_10</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-13 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_4&#x2F;">title_4</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-15 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_8&#x2F;">title_8</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-16 days']}</div>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_11&#x2F;">title_11</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-19 days']}</div>
</li>
<li class="list__row list__row--data">
<div class="list__col list__col--first" data-name="Image">
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_19&#x2F;">title_19</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-20 days']}</div>
</li>
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;1" aria-current="page">1</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;2&#x2F;" aria-label="Next&#x20;page">Next page</a>
</li>
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
    public static function provide30PublicUnlistedPrivateBlueprintTagGoodPage2DataCases(): iterable
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new DateTime('now', new DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            '30 blueprints public/unlisted/private - (visitor profile) - page 2' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/2/',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 2 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (public profile) - page 2' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/2/',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 2 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
<a class="list__link-on-placeholder" href="&#x2F;blueprint&#x2F;slug_30&#x2F;">
<svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
<use href="/sprite/sprite.svg#blueprint-placeholder"></use>
</svg>
</a>
</div>
<div class="list__col" data-name="Type">blueprint</div>
<div class="list__col" data-name="UE Version">4.0</div>
<div class="list__col" data-name="Title"><a class="list__link" href="&#x2F;blueprint&#x2F;slug_30&#x2F;">title_30</a></div>
<div class="list__col" data-name="Author"><a class="list__link" href="&#x2F;profile&#x2F;user_179&#x2F;">user_179 &lt;script&gt;alert(1)&lt;&#47;script&gt;</a></div>
<div class="list__col" data-name="Date">{$formattedDates['-30 days']}</div>
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
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (author profile) - page 2' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `tags`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '24'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '24'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '24'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '24'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '24'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '24'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '24'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '24'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '24'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '24'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '24'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '24'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '24'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '24'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '24'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '24'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '24'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '24'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '24'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '24'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '24'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '24'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '24'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '24'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '24'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '24'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '24'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '24'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '24'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '24'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '24'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '24'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '24'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '24'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '24'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '24'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '24'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '24'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '24'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '24'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '24'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '24'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '24'),
                               (159, 'slug_44', 'file_44', 'title_44', 1, utc_timestamp() - interval 44 day, utc_timestamp() - interval 44 day, 'public', null),
                               (159, 'slug_45', 'file_45', 'title_45', 1, utc_timestamp() - interval 45 day, utc_timestamp() - interval 45 day, 'public', '1')
                    SQL,
                ],
                'slug'        => '/tag/lo/2/',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Tag yo | Page 2 | This is a base title',
                    'description' => 'List of blueprints tagged as yo'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Tag <span class="block__title--emphasis">yo</span></h2>
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
</ul>
HTML,
                'contentPaginationHTML' => <<<'HTML'
<nav aria-label="Pagination" class="pagination">
<ul class="pagination__items">
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;tag&#x2F;lo&#x2F;1&#x2F;" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
HTML,
            ],
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('provideTagAccessDataCases')]
    #[DataProvider('provide3PublicUnlistedPrivateBlueprintTagNotGoodDataCases')]
    #[DataProvider('provide3PublicUnlistedPrivateBlueprintTagGoodDataCases')]
    #[DataProvider('provide30PublicUnlistedPrivateBlueprintTagGoodPage1DataCases')]
    #[DataProvider('provide30PublicUnlistedPrivateBlueprintTagGoodPage2DataCases')]
    public function testTagListGET(array $sqlQueries, string $slug, ?string $location, ?int $userID, ?array $contentHead, string $contentBlueprintsHTML, string $contentPaginationHTML): void
    {
        static::setDatabase();
        static::$db->truncateTables('blueprints');

        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        $sessionValues = [
            'set'    => [],
            'remove' => ['userID']
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

        $response = $this->getResponseFromApplication('GET', $slug, [], [], [], $queryParams);
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
        $this->doTestNavBarHasLinkBlueprintActive($response);

        $this->doTestHtmlMain($response, $contentBlueprintsHTML);
        $this->doTestHtmlMain($response, $contentPaginationHTML);
    }
}
