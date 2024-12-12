<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Tag;

use app\services\www\TagService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Session\Session;
use tests\Common;

class TagsTextareaTest extends TestCase
{
    use Common;

    public static function dataCasesEmptyTextarea(): array
    {
        return [
            'empty textarea - no tag before' => [
                'tagsSQLBefore' => null,
                'textarea'      => '',
                'tagsIDs'       => null,
                'tagsAfter'     => [],
            ],
            'empty textarea - tag before' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'      => '',
                'tagsIDs'       => null,
                'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
        ];
    }

    public static function dataCasesOneTagInTextarea(): array
    {
        return [
            '1 tag in textarea - no tag before' => [
                'tagsSQLBefore' => null,
                'textarea'      => 'a',
                'tagsIDs'       => '1',
                'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
            '1 tag in textarea - tag before - no creation' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'      => 'a',
                'tagsIDs'       => '1',
                'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
            ],
            '1 tag in textarea - tag before - 1 creation' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'      => 'b',
                'tagsIDs'       => '2',
                'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
        ];
    }

    public static function dataCasesTwoTagsInTextarea(): array
    {
        return [
            '2 tags in textarea - no tag before' => [
                'tagsSQLBefore' => null,
                'textarea'      => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tagsIDs'   => '1,2',
                'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 2 tag before - no creation' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a'), (2, 'b', 'b')",
                'textarea'      => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tagsIDs'   => '1,2',
                'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 1 tag (a) before - 1 creation' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
                'textarea'      => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tagsIDs'   => '1,2',
                'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
            ],
            '2 tags in textarea - 1 tag (b) before - 1 creation' => [
                'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'b', 'b')",
                'textarea'      => <<<TEXTAREA
                                     a
                                     b
                                     TEXTAREA,
                'tagsIDs'   => '1,2',
                'tagsAfter' => [['id' => '1', 'name' => 'b', 'slug' => 'b'], ['id' => '2', 'name' => 'a', 'slug' => 'a']],
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
    #[DataProvider('dataCasesEmptyTextarea')]
    #[DataProvider('dataCasesOneTagInTextarea')]
    #[DataProvider('dataCasesTwoTagsInTextarea')]
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

        $tagRows = static::$db->selectAll('SELECT * FROM tags');
        if (\PHP_MAJOR_VERSION >= 8 && \PHP_MINOR_VERSION >= 1) {
            foreach ($tagRows as $key => $value) {
                $tagRows[$key]['id'] = (string) $value['id'];
            }
        }

        static::assertSame($tagsAfter, $tagRows);
    }
}
