<?php

/* @noinspection HtmlUnknownTarget */

declare(strict_types=1);

namespace tests\www\Blueprint\View;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class BlueprintGETVersionsBlueprintTest extends TestCase
{
    use Common;

    /** @throws DatabaseException */
    public static function setUpBeforeClass(): void
    {
        static::setDatabaseEmptyStructure();
        static::addUsers();
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * Use for testing list blueprint's versions.
     *
     * @return array[]
     */
    public static function provideBlueprintGETVersionsBlueprintDataCases(): iterable
    {
        $date = '2020-01-01 01:01:01';
        $date1DayLater = '2020-01-02 01:01:01';

        yield '1 version' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', '" . $date . "', '" . $date . "')",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'            => 'slug_public',
            'hasListVersions' => false,
            'html'            => null,
        ];

        yield '2 versions - same day' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', '" . $date . "', '" . $date . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', '" . $date . "', '" . $date . "')",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'            => 'slug_public',
            'hasListVersions' => true,
            'html'            => <<<'HTML'
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 1, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--current">
<div class="blueprint__version-left">
<p>Second commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;diff&#x2F;2&#x2F;">Diff</a>
</div>
</li>
<li class="blueprint__version blueprint__version--last">
<div class="blueprint__version-left">
<p>First commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;">See</a>
</div>
</li>
</ol>
HTML
        ];

        yield '2 versions - not same day' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', '" . $date . "', '" . $date . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', '" . $date1DayLater . "', '" . $date1DayLater . "')",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'            => 'slug_public',
            'hasListVersions' => true,
            'html'            => <<<'HTML'
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 2, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--last blueprint__version--current">
<div class="blueprint__version-left">
<p>Second commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;diff&#x2F;2&#x2F;">Diff</a>
</div>
</li>
</ol>
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 1, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--last">
<div class="blueprint__version-left">
<p>First commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;">See</a>
</div>
</li>
</ol>
HTML
        ];

        yield '3 versions - 2 same day, 1 another day' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', '" . $date . "', '" . $date . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', '" . $date1DayLater . "', '" . $date1DayLater . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 3, 'Third commit', '" . $date1DayLater . "', '" . $date1DayLater . "')",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'            => 'slug_public',
            'hasListVersions' => true,
            'html'            => <<<'HTML'
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 2, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--current">
<div class="blueprint__version-left">
<p>Third commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;2&#x2F;diff&#x2F;3&#x2F;">Diff</a>
</div>
</li>
<li class="blueprint__version blueprint__version--last">
<div class="blueprint__version-left">
<p>Second commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;2&#x2F;">See</a>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;diff&#x2F;2&#x2F;">Diff</a>
</div>
</li>
</ol>
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 1, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--last">
<div class="blueprint__version-left">
<p>First commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;">See</a>
</div>
</li>
</ol>
HTML
        ];

        yield '3 versions but versions 2 is selected - 2 same day, 1 another day' => [
            'sqlQueries' => [
                'TRUNCATE TABLE blueprints',
                'TRUNCATE TABLE blueprints_version',
                "INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure, type, ue_version) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public', 'blueprint', '4.12')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 1, 'First commit', '" . $date . "', '" . $date . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 2, 'Second commit', '" . $date1DayLater . "', '" . $date1DayLater . "')",
                "INSERT INTO blueprints_version (id_blueprint, version, reason, created_at, published_at) VALUES (1, 3, 'Third commit', '" . $date1DayLater . "', '" . $date1DayLater . "')",
                "REPLACE INTO users (id, username, password, slug, email, created_at) VALUES (1, 'member', null, 'member', 'member@mail', utc_timestamp())",
            ],
            'slug'            => 'slug_public/2',
            'hasListVersions' => true,
            'html'            => <<<'HTML'
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 2, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version">
<div class="blueprint__version-left">
<p>Third commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;3&#x2F;">See</a>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;2&#x2F;diff&#x2F;3&#x2F;">Diff</a>
</div>
</li>
<li class="blueprint__version blueprint__version--last blueprint__version--current">
<div class="blueprint__version-left">
<p>Second commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;diff&#x2F;2&#x2F;">Diff</a>
</div>
</li>
</ol>
<div class="blueprint__versions-header">
<svg class="blueprint__version-svg">
<use href="/sprite/sprite.svg#icon-blueprintue"></use>
</svg>
<span class="blueprint__version-date">January 1, 2020</span>
</div>
<ol class="blueprint__versions" reversed>
<li class="blueprint__version blueprint__version--last">
<div class="blueprint__version-left">
<p>First commit</p>
</div>
<div>
<a class="block__link block__link--no-margin" href="&#x2F;blueprint&#x2F;slug_public&#x2F;1&#x2F;">See</a>
</div>
</li>
</ol>
HTML
        ];
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     */
    #[DataProvider('provideBlueprintGETVersionsBlueprintDataCases')]
    public function testBlueprintGETVersionsBlueprint(array $sqlQueries, string $slug, bool $hasListVersions, ?string $html): void
    {
        // sql queries
        static::setDatabase();
        foreach ($sqlQueries as $sqlQuery) {
            static::$db->exec($sqlQuery);
        }

        // init session
        $this->getResponseFromApplication('GET', '/');

        // get blueprint page
        $response = $this->getResponseFromApplication('GET', '/blueprint/' . $slug . '/');
        $this->doTestHasResponseWithStatusCode($response, 200);

        if ($hasListVersions) {
            $this->doTestHtmlMain($response, '<span class="blueprint__versions-title">Versions</span>');
            $this->doTestHtmlMain($response, $html);
        } else {
            $this->doTestHtmlMainNot($response, '<span class="blueprint__versions-title">Versions</span>');
        }
    }
}
