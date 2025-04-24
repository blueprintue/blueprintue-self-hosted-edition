<?php

/* @noinspection PhpMethodNamingConventionInspection */
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

class SearchListTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /** @throws \Exception */
    public static function dataCases(): array
    {
        $cases = [];

        $cases = static::addSearchError($cases);
        $cases = static::addSearchQuery($cases);
        $cases = static::addSearchQueryType($cases);
        $cases = static::addSearchQueryTypeVersion($cases);
        $cases = static::addSearchQueryVersion($cases);
        $cases = static::addSearchType($cases);
        $cases = static::addSearchTypeVersion($cases);

        return static::addSearchVersion($cases);
    }

    /** @throws \Exception */
    protected static function addSearchError(array $cases): array
    {
        $cases['Search - Error Invalid Term'] = [
            'sqlQueries'            => [],
            'slug'                  => '/search/?form-search-input-query=tle&aze=' . \chr(99999999),
            'location'              => '/',
            'userID'                => null,
            'contentHead'           => [],
            'contentBlueprintsHTML' => '',
            'contentPaginationHTML' => ''
        ];

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchQuery(array $cases): array
    {
        $searchQuery = new SearchQueryCases();

        foreach ($searchQuery::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Query - ' . $k] = $v;
        }
        foreach ($searchQuery::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Query - ' . $k] = $v;
        }
        foreach ($searchQuery::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Query - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchQueryType(array $cases): array
    {
        $searchQueryType = new SearchQueryTypeCases();

        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateAnimationBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateBehaviorTreeBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateMaterialBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateMetasoundBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivateNiagaraBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases3PublicUnlistedPrivatePCGBlueprint() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }
        foreach ($searchQueryType::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Query + Type - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchQueryTypeVersion(array $cases): array
    {
        $searchQueryTypeVersion = new SearchQueryTypeVersionCases();

        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateAnimationBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateBehaviorTreeBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateMaterialBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateMetasoundBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivateNiagaraBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases3PublicUnlistedPrivatePCGBlueprint() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }
        foreach ($searchQueryTypeVersion::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Query + Type + Version - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchQueryVersion(array $cases): array
    {
        $searchQueryVersion = new SearchQueryVersionCases();

        foreach ($searchQueryVersion::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Query + Version - ' . $k] = $v;
        }
        foreach ($searchQueryVersion::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Query + Version - ' . $k] = $v;
        }
        foreach ($searchQueryVersion::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Query + Version - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchType(array $cases): array
    {
        $searchType = new SearchTypeCases();

        foreach ($searchType::dataCases3PublicUnlistedPrivateAnimationBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivateBehaviorTreeBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivateMaterialBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivateMetasoundBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivateNiagaraBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases3PublicUnlistedPrivatePCGBlueprint() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }
        foreach ($searchType::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Type - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchTypeVersion(array $cases): array
    {
        $searchTypeVersion = new SearchTypeVersionCases();

        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateAnimationBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateBehaviorTreeBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateMaterialBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateMetasoundBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivateNiagaraBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases3PublicUnlistedPrivatePCGBlueprint() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }
        foreach ($searchTypeVersion::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Type + Version - ' . $k] = $v;
        }

        return $cases;
    }

    /** @throws \Exception */
    protected static function addSearchVersion(array $cases): array
    {
        $searchVersion = new SearchVersionCases();

        foreach ($searchVersion::dataCases3PublicUnlistedPrivateBlueprint() as $k => $v) {
            $cases['Search - Version - ' . $k] = $v;
        }
        foreach ($searchVersion::dataCases30PublicUnlistedPrivateBlueprintPage1() as $k => $v) {
            $cases['Search - Version - ' . $k] = $v;
        }
        foreach ($searchVersion::dataCases30PublicUnlistedPrivateBlueprintPage2() as $k => $v) {
            $cases['Search - Version - ' . $k] = $v;
        }

        return $cases;
    }

    /**
     * @dataProvider dataCases
     *
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     * @throws RouterException
     * @throws SecurityException
     */
    #[DataProvider('dataCases')]
    public function testSearchQueryListGET(array $sqlQueries, string $slug, ?string $location, ?int $userID, ?array $contentHead, string $contentBlueprintsHTML, string $contentPaginationHTML): void
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
