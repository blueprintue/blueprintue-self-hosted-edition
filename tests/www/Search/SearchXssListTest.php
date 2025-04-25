<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Search;

use app\helpers\Helper;
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
class SearchXssListTest extends TestCase
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

        static::$db->exec(<<<'SQL'
            INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `description`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', '<script>alert(1)</script>'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', '<script>alert(1)</script>'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', '<script>alert(1)</script>'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', '<script>alert(1)</script>'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', '<script>alert(1)</script>'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', '<script>alert(1)</script>'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', '<script>alert(1)</script>'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', '<script>alert(1)</script>'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', '<script>alert(1)</script>'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', '<script>alert(1)</script>'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', '<script>alert(1)</script>')
            SQL);
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
    public static function provideDataCases(): iterable
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new \DateTime('now', new \DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            '30 blueprints public/unlisted/private - xss form-search-input-query' => [
                'sqlQueries'  => [],
                'slugInput'   => '/search/?form-search-input-query=<script>alert(1)</script>',
                'slugOutput'  => '/search/?form-search-input-query=<script>alert(1)</script>&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "<script>alert(1)</script>" | Page 1 | This is a base title',
                    'description' => 'Search "<script>alert(1)</script>" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis">&lt;script&gt;alert(1)&lt;&#47;script&gt;</span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - xss query' => [
                'sqlQueries'  => [],
                'slugInput'   => '/search/?query=<script>alert(1)</script>',
                'slugOutput'  => '/search/?form-search-input-query=<script>alert(1)</script>&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "<script>alert(1)</script>" | Page 1 | This is a base title',
                    'description' => 'Search "<script>alert(1)</script>" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis">&lt;script&gt;alert(1)&lt;&#47;script&gt;</span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - xss in all fields' => [
                'sqlQueries'  => [],
                'slugInput'   => '/search/?form-search-input-query=<script>alert(1)</script>&form-search-select-type=<script>alert(1)</script>&form-search-select-ue_version=<script>alert(1)</script>&page=<script>alert(1)</script>',
                'slugOutput'  => '/search/?form-search-input-query=<script>alert(1)</script>&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "<script>alert(1)</script>" | Page 1 | This is a base title',
                    'description' => 'Search "<script>alert(1)</script>" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis">&lt;script&gt;alert(1)&lt;&#47;script&gt;</span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-input-query&#x3D;&lt;script&gt;alert&#x28;1&#x29;&lt;&#x2F;script&gt;&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
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
    #[DataProvider('provideDataCases')]
    public function testSearchXssListGET(array $sqlQueries, string $slugInput, string $slugOutput, ?string $location, ?int $userID, ?array $contentHead, string $contentBlueprintsHTML, string $contentPaginationHTML): void
    {
        static::setDatabase();

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

        $parsedUrl = \parse_url($slugInput);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            \parse_str($parsedUrl['query'], $queryParams);
        }

        $response = $this->getResponseFromApplication('GET', $slugInput, [], [], [], $queryParams);
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
        $this->doTestHtmlMain($response, $this->getHTMLFieldQuery($queryParams));
        $this->doTestHtmlMain($response, $this->getHTMLFieldType($queryParams));
        $this->doTestHtmlMain($response, $this->getHTMLFieldVersion($queryParams));
    }

    /** @throws SecurityException */
    protected function getHTMLFieldQuery(array $queryParams): string
    {
        $v = Security::escAttr($queryParams['query'] ?? $queryParams['form-search-input-query'] ?? '');

        return <<<HTML
<div class="form__element home__form--title">
<label class="form__label" for="form-search-input-query" id="form-search-label-query">Terms to search</label>
<input aria-invalid="false" aria-labelledby="form-search-label-query" class="form__input" id="form-search-input-query" name="form-search-input-query" type="text" value="{$v}"/>
</div>
HTML;
    }

    protected function getHTMLFieldType(array $queryParams): string
    {
        $value = $queryParams['form-search-select-type'] ?? '';

        $all = ($value === '') ? ' selected="selected"' : '';
        $animation = ($value === 'animation') ? ' selected="selected"' : '';
        $behaviorTree = ($value === 'behavior-tree') ? ' selected="selected"' : '';
        $blueprint = ($value === 'blueprint') ? ' selected="selected"' : '';
        $material = ($value === 'material') ? ' selected="selected"' : '';
        $metasound = ($value === 'metasound') ? ' selected="selected"' : '';
        $niagara = ($value === 'niagara') ? ' selected="selected"' : '';
        $pcg = ($value === 'pcg') ? ' selected="selected"' : '';

        if ($animation === '' && $behaviorTree === '' && $blueprint === '' && $material === '' && $metasound === '' && $niagara === '' && $pcg === '') {
            $all = ' selected="selected"';
        }

        return <<<HTML
<div class="form__element home__form--selectors">
<label class="form__label" for="form-search-select-type" id="form-search-label-type">Type</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-search-label-type" aria-required="true" class="form__input form__input--select" id="form-search-select-type" name="form-search-select-type">
<option value=""{$all}>All</option>
<option value="animation"{$animation}>Animation</option>
<option value="behavior-tree"{$behaviorTree}>Behavior Tree</option>
<option value="blueprint"{$blueprint}>Blueprint</option>
<option value="material"{$material}>Material</option>
<option value="metasound"{$metasound}>Metasound</option>
<option value="niagara"{$niagara}>Niagara</option>
<option value="pcg"{$pcg}>PCG</option>
</select>
</div>
</div>
HTML;
    }

    /** @throws SecurityException */
    protected function getHTMLFieldVersion(array $queryParams): string
    {
        $value = $queryParams['form-search-select-ue_version'] ?? '';

        $all = '';
        if (\in_array($value, Helper::getAllUEVersion(), true) === false) {
            $all = ' selected="selected"';
        }

        $str = '';
        foreach (Helper::getAllUEVersion() as $ueVersion) {
            $str .= '<option value="' . Security::escAttr($ueVersion) . '"' . (($value === $ueVersion) ? ' selected="selected"' : '') . '>' . Security::escHTML($ueVersion) . '</option>' . "\n";
        }

        return <<<HTML
<div class="form__element home__form--selectors">
<label class="form__label" for="form-search-select-ue_version" id="form-search-label-ue_version">UE version</label>
<div class="form__container form__container--select">
<select aria-invalid="false" aria-labelledby="form-search-label-ue_version" aria-required="true" class="form__input form__input--select" id="form-search-select-ue_version" name="form-search-select-ue_version">
<option value=""{$all}>All</option>
{$str}</select>
</div>
</div>
HTML;
    }
}
