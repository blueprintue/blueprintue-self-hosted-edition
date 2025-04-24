<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Lists;

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

class TagsListTest extends TestCase
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

        static::$db->insert("INSERT INTO tags (`id`, `name`, `slug`) VALUES (14, 'aze', 'qwe'), (24, 'yo', 'lo'), (34, 'pa<script>alert(1);</script>', 'pa666<script>alert(1);</script>')");
        static::$db->insert("INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_public', 'a', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'public')");
        static::$db->insert("INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (1, 'slug_unlisted', 'b', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'unlisted')");
        static::$db->insert("INSERT INTO blueprints (id_author, slug, file_id, title, current_version, created_at, published_at, exposure) VALUES (179, 'slug_private', 'c', '<script>alert(1)</script>my title', 1, utc_timestamp(), utc_timestamp(), 'private')");
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
    public static function dataCases(): array
    {
        return [
            'empty page - no tags linked to blueprints (tags null)' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET tags = NULL WHERE id > 0'
                ],
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Blueprint\'s Tags | This is a base title',
                    'description' => 'List of tags associated to blueprints'
                ],
                'contentHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
<p>No tags for the moment</p>
</div>
</div>
HTML,
            ],
            'empty page - no tags linked to blueprints (tags string empty)' => [
                'sqlQueries' => [
                    "UPDATE blueprints SET tags = '' WHERE id > 0"
                ],
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Blueprint\'s Tags | This is a base title',
                    'description' => 'List of tags associated to blueprints'
                ],
                'contentHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
<p>No tags for the moment</p>
</div>
</div>
HTML,
            ],
            'empty page - tags linked to private / unlisted blueprints' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET tags = NULL WHERE id = 1',
                    "UPDATE blueprints SET tags = '14' WHERE id > 1"
                ],
                'userID'      => null,
                'contentHead' => [
                    'title'       => 'Blueprint\'s Tags | This is a base title',
                    'description' => 'List of tags associated to blueprints'
                ],
                'contentHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
<p>No tags for the moment</p>
</div>
</div>
HTML,
            ],
            '1 tag - tags linked to private / unlisted blueprints - user private blueprint' => [
                'sqlQueries' => [
                    'UPDATE blueprints SET tags = NULL WHERE id = 1',
                    "UPDATE blueprints SET tags = '14' WHERE id = 2",
                    "UPDATE blueprints SET tags = '34' WHERE id = 3"
                ],
                'userID'      => 179,
                'contentHead' => [
                    'title'       => 'Blueprint\'s Tags | This is a base title',
                    'description' => 'List of tags associated to blueprints'
                ],
                'contentHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
<div class="tags block__markdown">
<div class="tags__list">
<h3>P</h3>
<ul>
<li><a href="/tag/pa666&lt;script&gt;alert&#x28;1&#x29;&#x3B;&lt;&#x2F;script&gt;/1/">pa&lt;script&gt;alert(1);&lt;&#47;script&gt;</a></li>
</ul>
</div>
</div>
</div>
</div>
HTML,
            ],
            '3 tags - tags linked to public blueprints' => [
                'sqlQueries' => [
                    "UPDATE blueprints SET tags = '14,24' WHERE id = 1",
                    "UPDATE blueprints SET tags = '14' WHERE id = 2",
                    "UPDATE blueprints SET tags = '34' WHERE id = 3"
                ],
                'userID'      => 169,
                'contentHead' => [
                    'title'       => 'Blueprint\'s Tags | This is a base title',
                    'description' => 'List of tags associated to blueprints'
                ],
                'contentHTML' => <<<HTML
<div class="block__container block__container--first block__container--last">
<div class="block__element">
<h2 class="block__title">Blueprint's <span class="block__title--emphasis">tags</span></h2>
<div class="tags block__markdown">
<div class="tags__list">
<h3>A</h3>
<ul>
<li><a href="/tag/qwe/1/">aze</a></li>
</ul>
</div>
<div class="tags__list">
<h3>Y</h3>
<ul>
<li><a href="/tag/lo/1/">yo</a></li>
</ul>
</div>
</div>
</div>
</div>
HTML,
            ],
        ];
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
    public function testTagsListGET(array $sqlQueries, ?int $userID, ?array $contentHead, string $contentHTML): void
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

        $this->getResponseFromApplication('GET', '/contact/', [], $sessionValues);

        $response = $this->getResponseFromApplication('GET', '/tags/', [], [], []);
        $this->doTestHasResponseWithStatusCode($response, 200);
        $this->doTestHtmlHead($response, [
            'title'       => Security::escHTML($contentHead['title']),
            'description' => Security::escAttr($contentHead['description'])
        ]);
        $this->doTestNavBarIsComplete($response);
        $this->doTestNavBarHasNoLinkActive($response);

        $this->doTestHtmlMain($response, $contentHTML);
    }
}
