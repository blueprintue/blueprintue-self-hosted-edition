<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Tag;

use app\services\www\TagService;
use PHPUnit\Framework\TestCase;
use Rancoud\Session\Session;
use tests\Common;

class TagsTextareaTest extends TestCase
{
    use Common;

    public function dataCasesEmptyTextarea(): array
    {
        return [
            'empty textarea - no tag before' => [
                'tags_sql_before' => null,
                'textarea'        => '',
                'tags_ids'        => null,
                'tags_after'      => [],
            ],
            'empty textarea - tag before' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'        => '',
                'tags_ids'        => null,
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
        ];
    }

    public function dataCasesOneTagInTextarea(): array
    {
        return [
            '1 tag in textarea - no tag before' => [
                'tags_sql_before' => null,
                'textarea'        => 'a',
                'tags_ids'        => '1',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
            '1 tag in textarea - tag before - no creation' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'        => 'a',
                'tags_ids'        => '1',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
            '1 tag in textarea - tag before - 1 creation' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'        => 'b',
                'tags_ids'        => '2',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
        ];
    }

    public function dataCasesTwoTagsInTextarea(): array
    {
        return [
            '2 tags in textarea - no tag before' => [
                'tags_sql_before' => null,
                'textarea'        => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tags_ids'        => '1,2',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 2 tag before - no creation' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a'), (2, 'b', 'b')",
                'textarea'        => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tags_ids'        => '1,2',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 1 tag (a) before - 1 creation' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'        => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tags_ids'        => '1,2',
                'tags_after'      => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 1 tag (b) before - 1 creation' => [
                'tags_sql_before' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'b', 'b')",
                'textarea'        => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tags_ids'        => '1,2',
                'tags_after'      => [['id' => '1', 'name' => 'b', 'slug' => 'b'], ['id' => '2', 'name' => 'a', 'slug' => 'a']],
            ],
        ];
    }

    /**
     * @dataProvider dataCasesEmptyTextarea
     * @dataProvider dataCasesOneTagInTextarea
     * @dataProvider dataCasesTwoTagsInTextarea
     *
     * @param string|null $tagsSQLBefore
     * @param string      $textarea
     * @param string|null $tagsIDs
     * @param array       $tagsAfter
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Router\RouterException
     */
    public function testCreate(?string $tagsSQLBefore, string $textarea, ?string $tagsIDs, array $tagsAfter): void
    {
        static::setDatabase();
        static::$db->truncateTables('tags');

        if ($tagsSQLBefore !== null) {
            static::$db->exec($tagsSQLBefore);
        }

        if (Session::hasStarted() && Session::isReadOnly() === false) {
            Session::commit();
        }

        $this->getResponseFromApplication('GET', '/contact/');

        if (Session::hasStarted() && Session::isReadOnly() === false) {
            Session::commit();
        }

        static::assertSame($tagsIDs, TagService::createAndFindTagsWithTextareaTags($textarea));
        static::assertSame($tagsAfter, static::$db->selectAll('SELECT * FROM tags'));
    }
}
