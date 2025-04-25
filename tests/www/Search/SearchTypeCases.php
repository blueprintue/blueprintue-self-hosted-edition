<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Search;

use tests\Common;

class SearchTypeCases
{
    use Common;

    /**
     * @throws \Exception
     *
     * @return array[]
     */
    public static function dataCases3PublicUnlistedPrivateAnimationBlueprint(): array
    {
        return [
            '3 animation blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 animation blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">animation</div>
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
            '3 animation blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">animation</div>
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
            '3 animation blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'animation'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'animation')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=animation&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">animation</div>
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
<div class="list__col" data-name="Type">animation</div>
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
<div class="list__col" data-name="Type">animation</div>
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
    public static function dataCases3PublicUnlistedPrivateBehaviorTreeBlueprint(): array
    {
        return [
            '3 behavior-tree blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 behavior-tree blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">behavior<br/>tree</div>
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
            '3 behavior-tree blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">behavior<br/>tree</div>
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
            '3 behavior-tree blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'behavior_tree'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'behavior_tree'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'behavior_tree')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=behavior-tree&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">behavior<br/>tree</div>
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
<div class="list__col" data-name="Type">behavior<br/>tree</div>
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
<div class="list__col" data-name="Type">behavior<br/>tree</div>
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
    public static function dataCases3PublicUnlistedPrivateBlueprint(): array
    {
        return [
            '3 blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'blueprint'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
    public static function dataCases3PublicUnlistedPrivateMaterialBlueprint(): array
    {
        return [
            '3 material blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 material blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">material</div>
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
            '3 material blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">material</div>
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
            '3 material blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'material'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'material'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'material')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=material&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">material</div>
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
<div class="list__col" data-name="Type">material</div>
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
<div class="list__col" data-name="Type">material</div>
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
    public static function dataCases3PublicUnlistedPrivateMetasoundBlueprint(): array
    {
        return [
            '3 metasound blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 metasound blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">metasound</div>
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
            '3 metasound blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">metasound</div>
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
            '3 metasound blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'metasound'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'metasound'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'metasound')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=metasound&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">metasound</div>
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
<div class="list__col" data-name="Type">metasound</div>
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
<div class="list__col" data-name="Type">metasound</div>
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
    public static function dataCases3PublicUnlistedPrivateNiagaraBlueprint(): array
    {
        return [
            '3 niagara blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 niagara blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">niagara</div>
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
            '3 niagara blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">niagara</div>
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
            '3 niagara blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'niagara'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'niagara'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'niagara')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=niagara&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">niagara</div>
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
<div class="list__col" data-name="Type">niagara</div>
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
<div class="list__col" data-name="Type">niagara</div>
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
    public static function dataCases3PublicUnlistedPrivatePCGBlueprint(): array
    {
        return [
            '3 pcg blueprints public/unlisted/private - created but not published - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - created but not published - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - created but not published - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - deleted - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - deleted - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - deleted - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `deleted_at`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', utc_timestamp(), 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', utc_timestamp(), 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', utc_timestamp(), 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
            '3 pcg blueprints public/unlisted/private - (visitor profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">pcg</div>
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
            '3 pcg blueprints public/unlisted/private - (public profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">pcg</div>
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
            '3 pcg blueprints public/unlisted/private - (author profile)' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`) VALUES
                                           (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                                           (159, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp(), utc_timestamp(), 'public', 'pcg'),
                                           (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp(), utc_timestamp(), 'unlisted', 'pcg'),
                                           (159, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp(), utc_timestamp(), 'private', 'pcg')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=pcg&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<'HTML'
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<div class="list__col" data-name="Type">pcg</div>
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
<div class="list__col" data-name="Type">pcg</div>
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
<div class="list__col" data-name="Type">pcg</div>
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
    public static function dataCases30PublicUnlistedPrivateBlueprintPage1(): array
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new \DateTime('now', new \DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            '30 blueprints public/unlisted/private - (visitor profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (public profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
</li>
</ul>
HTML,
            ],
            '30 blueprints public/unlisted/private - (author profile) - page 1' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=1',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 1 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Page&#x20;2">2</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;2" aria-label="Next&#x20;page">Next page</a>
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
    public static function dataCases30PublicUnlistedPrivateBlueprintPage2(): array
    {
        $formattedDates = [];
        for ($i = 0; $i < 46; ++$i) {
            $formattedDates['-' . $i . ' days'] = static::getSince((new \DateTime('now', new \DateTimeZone('UTC')))->modify('-' . $i . ' days')->format('Y-m-d H:i:s'));
        }

        return [
            '30 blueprints public/unlisted/private - (visitor profile) - page 2' => [
                'sqlQueries' => [
                    <<<'SQL'
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=2',
                'location'    => null,
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Search "" | Page 2 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Page&#x20;1">1</a>
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
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=2',
                'location'    => null,
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Search "" | Page 2 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Page&#x20;1">1</a>
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
                    INSERT INTO blueprints (`id_author`, `slug`, `file_id`, `title`, `current_version`, `created_at`, `published_at`, `exposure`, `type`)
                        VALUES (179, 'slug_1', 'file_1', 'title_1', 1, utc_timestamp() - interval 2 day, utc_timestamp() - interval 2 day, 'public', 'blueprint'),
                               (159, 'slug_500', 'file_500', 'title_500', 1, utc_timestamp(), utc_timestamp(), 'public', 'animation'),
                               (159, 'slug_2', 'file_2', 'title_2', 1, utc_timestamp() - interval 10 day, utc_timestamp() - interval 10 day, 'public', 'blueprint'),
                               (169, 'slug_3', 'file_3', 'title_3', 1, utc_timestamp() - interval 3 day, utc_timestamp() - interval 3 day, 'public', 'blueprint'),
                               (179, 'slug_4', 'file_4', 'title_4', 1, utc_timestamp() - interval 15 day, utc_timestamp() - interval 15 day, 'public', 'blueprint'),
                               (159, 'slug_5', 'file_5', 'title_5', 1, utc_timestamp() - interval 8 day, utc_timestamp() - interval 8 day, 'public', 'blueprint'),
                               (179, 'slug_6', 'file_6', 'title_6', 1, utc_timestamp() - interval 9 day, utc_timestamp() - interval 9 day, 'public', 'blueprint'),
                               (159, 'slug_7', 'file_7', 'title_7', 1, utc_timestamp() - interval 18 day, utc_timestamp() - interval 18 day, 'private', 'blueprint'),
                               (179, 'slug_8', 'file_8', 'title_8', 1, utc_timestamp() - interval 16 day, utc_timestamp() - interval 16 day, 'public', 'blueprint'),
                               (159, 'slug_9', 'file_9', 'title_9', 1, utc_timestamp() - interval 14 day, utc_timestamp() - interval 14 day, 'public', 'blueprint'),
                               (179, 'slug_10', 'file_10', 'title_10', 1, utc_timestamp() - interval 13 day, utc_timestamp() - interval 13 day, 'public', 'blueprint'),
                               (179, 'slug_11', 'file_11', 'title_11', 1, utc_timestamp() - interval 19 day, utc_timestamp() - interval 19 day, 'public', 'blueprint'),
                               (169, 'slug_12', 'file_12', 'title_12', 1, utc_timestamp() - interval 12 day, utc_timestamp() - interval 12 day, 'public', 'blueprint'),
                               (159, 'slug_13', 'file_13', 'title_13', 1, utc_timestamp() - interval 4 day, utc_timestamp() - interval 4 day, 'private', 'blueprint'),
                               (169, 'slug_14', 'file_14', 'title_14', 1, utc_timestamp() - interval 5 day, utc_timestamp() - interval 5 day, 'public', 'blueprint'),
                               (159, 'slug_15', 'file_15', 'title_15', 1, utc_timestamp() - interval 1 day, utc_timestamp() - interval 1 day, 'public', 'blueprint'),
                               (179, 'slug_16', 'file_16', 'title_16', 1, utc_timestamp() - interval 11 day, utc_timestamp() - interval 11 day, 'public', 'blueprint'),
                               (159, 'slug_17', 'file_17', 'title_17', 1, utc_timestamp() - interval 6 day, utc_timestamp() - interval 6 day, 'private', 'blueprint'),
                               (159, 'slug_18', 'file_18', 'title_18', 1, utc_timestamp() - interval 17 day, utc_timestamp() - interval 17 day, 'public', 'blueprint'),
                               (179, 'slug_19', 'file_19', 'title_19', 1, utc_timestamp() - interval 20 day, utc_timestamp() - interval 20 day, 'public', 'blueprint'),
                               (169, 'slug_20', 'file_20', 'title_20', 1, utc_timestamp() - interval 7 day, utc_timestamp() - interval 7 day, 'public', 'blueprint'),
                               (159, 'slug_21', 'file_21', 'title_21', 1, utc_timestamp() - interval 21 day, utc_timestamp() - interval 21 day, 'public', 'blueprint'),
                               (159, 'slug_22', 'file_22', 'title_22', 1, utc_timestamp() - interval 22 day, utc_timestamp() - interval 22 day, 'private', 'blueprint'),
                               (159, 'slug_23', 'file_23', 'title_23', 1, utc_timestamp() - interval 23 day, utc_timestamp() - interval 23 day, 'public', 'blueprint'),
                               (159, 'slug_24', 'file_24', 'title_24', 1, utc_timestamp() - interval 24 day, utc_timestamp() - interval 24 day, 'private', 'blueprint'),
                               (159, 'slug_25', 'file_25', 'title_25', 1, utc_timestamp() - interval 25 day, utc_timestamp() - interval 25 day, 'public', 'blueprint'),
                               (159, 'slug_26', 'file_26', 'title_26', 1, utc_timestamp() - interval 26 day, utc_timestamp() - interval 26 day, 'private', 'blueprint'),
                               (159, 'slug_27', 'file_27', 'title_27', 1, utc_timestamp() - interval 27 day, utc_timestamp() - interval 27 day, 'public', 'blueprint'),
                               (159, 'slug_28', 'file_28', 'title_28', 1, utc_timestamp() - interval 28 day, utc_timestamp() - interval 28 day, 'private', 'blueprint'),
                               (159, 'slug_29', 'file_29', 'title_29', 1, utc_timestamp() - interval 29 day, utc_timestamp() - interval 29 day, 'public', 'blueprint'),
                               (179, 'slug_30', 'file_30', 'title_30', 1, utc_timestamp() - interval 30 day, utc_timestamp() - interval 30 day, 'private', 'blueprint'),
                               (159, 'slug_31', 'file_31', 'title_31', 1, utc_timestamp() - interval 31 day, utc_timestamp() - interval 31 day, 'public', 'blueprint'),
                               (159, 'slug_32', 'file_32', 'title_32', 1, utc_timestamp() - interval 32 day, utc_timestamp() - interval 32 day, 'private', 'blueprint'),
                               (159, 'slug_33', 'file_33', 'title_33', 1, utc_timestamp() - interval 33 day, utc_timestamp() - interval 33 day, 'public', 'blueprint'),
                               (169, 'slug_34', 'file_34', 'title_34', 1, utc_timestamp() - interval 34 day, utc_timestamp() - interval 34 day, 'private', 'blueprint'),
                               (159, 'slug_35', 'file_35', 'title_35', 1, utc_timestamp() - interval 35 day, utc_timestamp() - interval 35 day, 'public', 'blueprint'),
                               (159, 'slug_36', 'file_36', 'title_36', 1, utc_timestamp() - interval 36 day, utc_timestamp() - interval 36 day, 'private', 'blueprint'),
                               (159, 'slug_37', 'file_37', 'title_37', 1, utc_timestamp() - interval 37 day, utc_timestamp() - interval 37 day, 'public', 'blueprint'),
                               (159, 'slug_38', 'file_38', 'title_38', 1, utc_timestamp() - interval 38 day, utc_timestamp() - interval 38 day, 'private', 'blueprint'),
                               (159, 'slug_39', 'file_39', 'title_39', 1, utc_timestamp() - interval 39 day, utc_timestamp() - interval 39 day, 'public', 'blueprint'),
                               (169, 'slug_40', 'file_40', 'title_40', 1, utc_timestamp() - interval 40 day, utc_timestamp() - interval 40 day, 'private', 'blueprint'),
                               (159, 'slug_41', 'file_41', 'title_41', 1, utc_timestamp() - interval 41 day, utc_timestamp() - interval 41 day, 'public', 'blueprint'),
                               (159, 'slug_42', 'file_42', 'title_42', 1, utc_timestamp() - interval 42 day, utc_timestamp() - interval 42 day, 'public', 'blueprint'),
                               (159, 'slug_43', 'file_43', 'title_43', 1, utc_timestamp() - interval 43 day, utc_timestamp() - interval 43 day, 'public', 'blueprint')
                    SQL,
                ],
                'slug'        => '/search/?form-search-select-type=blueprint&page=2',
                'location'    => null,
                'userID'      => 159,
                'contentHead' => [
                    'title'       => 'Search "" | Page 2 | This is a base title',
                    'description' => 'Search "" in blueprints pasted'
                ],
                'contentBlueprintsHTML' => <<<HTML
<div class="block__container block__container--white-grey block__container--shadow-top block__container--last">
<div class="block__element">
<h2 class="block__title">Search Results <span class="block__title--emphasis"></span></h2>
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
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Previous&#x20;page">Previous page</a>
</li>
<li class="pagination__item">
<a class="pagination__link" href="&#x2F;search&#x2F;&#x3F;form-search-select-type&#x3D;blueprint&amp;page&#x3D;1" aria-label="Page&#x20;1">1</a>
</li>
<li class="pagination__item pagination__item--current">
<a class="pagination__link pagination__link--current" href="&#x23;" aria-label="Page&#x20;2" aria-current="page">2</a>
</li>
</ul>
HTML,
            ],
        ];
    }
}
