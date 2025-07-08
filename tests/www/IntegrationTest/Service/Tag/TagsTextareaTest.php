<?php

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Tag;

use app\services\www\TagService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class TagsTextareaTest extends TestCase
{
    use Common;

    public static function provideCreateDataCases(): iterable
    {
        yield 'empty textarea - no tag before' => [
            'tagsSQLBefore' => null,
            'textarea'      => '',
            'tagsIDs'       => null,
            'tagsAfter'     => [],
        ];

        yield 'empty textarea - tag before' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
            'textarea'      => '',
            'tagsIDs'       => null,
            'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
        ];
    }

    public static function provideOneTagInTextareaDataCases(): iterable
    {
        yield '1 tag in textarea - no tag before' => [
            'tagsSQLBefore' => null,
            'textarea'      => 'a',
            'tagsIDs'       => '1',
            'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
        ];

        yield '1 tag in textarea - tag before - no creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
            'textarea'      => 'a',
            'tagsIDs'       => '1',
            'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a']],
        ];

        yield '1 tag in textarea - tag before - 1 creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
            'textarea'      => 'b',
            'tagsIDs'       => '2',
            'tagsAfter'     => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
        ];
    }

    public static function provideTwoTagsInTextareaDataCases(): iterable
    {
        yield '2 tags in textarea - no tag before' => [
            'tagsSQLBefore' => null,
            'textarea'      => <<<'TEXTAREA'
                a
                b
                TEXTAREA,
            'tagsIDs'   => '1,2',
            'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
        ];

        yield '2 tags in textarea - 2 tag before - no creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a'), (2, 'b', 'b')",
            'textarea'      => <<<'TEXTAREA'
                a
                b
                TEXTAREA,
            'tagsIDs'   => '1,2',
            'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
        ];

        yield '2 tags in textarea - 1 tag (a) before - 1 creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'a', 'a')",
            'textarea'      => <<<'TEXTAREA'
                a
                b
                TEXTAREA,
            'tagsIDs'   => '1,2',
            'tagsAfter' => [['id' => '1', 'name' => 'a', 'slug' => 'a'], ['id' => '2', 'name' => 'b', 'slug' => 'b']],
        ];

        yield '2 tags in textarea - 1 tag (b) before - 1 creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'b', 'b')",
            'textarea'      => <<<'TEXTAREA'
                a
                b
                TEXTAREA,
            'tagsIDs'   => '1,2',
            'tagsAfter' => [['id' => '1', 'name' => 'b', 'slug' => 'b'], ['id' => '2', 'name' => 'a', 'slug' => 'a']],
        ];
    }

    public static function provideCaseSensitiveTagsDataCases(): iterable
    {
        yield '4 tags in textarea - 4 tags before - no creation' => [
            'tagsSQLBefore' => "INSERT INTO tags (`id`, `name`, `slug`) VALUES (1, 'Camera', 'camera'), (2, 'Line Trace', 'line-trace'), (3, 'Test-Debug', 'test-debug'), (4, 'WASD', 'wasd')",
            'textarea'      => <<<'TEXTAREA'
                camera
                line-trace
                test debug
                WASD
                TEXTAREA,
            'tagsIDs'   => '1,2,3,4',
            'tagsAfter' => [['id' => '1', 'name' => 'Camera', 'slug' => 'camera'], ['id' => '2', 'name' => 'Line Trace', 'slug' => 'line-trace'], ['id' => '3', 'name' => 'Test-Debug', 'slug' => 'test-debug'], ['id' => '4', 'name' => 'WASD', 'slug' => 'wasd']],
        ];

        yield '4 tags in textarea (2 duplicate) - 0 tag before - 2 creation' => [
            'tagsSQLBefore' => null,
            'textarea'      => <<<'TEXTAREA'
                4.19
                4-19
                Third Person Movement
                third-person-movement
                TEXTAREA,
            'tagsIDs'   => '1,2',
            'tagsAfter' => [['id' => '1', 'name' => '4.19', 'slug' => '4-19'], ['id' => '2', 'name' => 'third person movement', 'slug' => 'third-person-movement']],
        ];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Model\ModelException
     * @throws \Rancoud\Router\RouterException
     */
    #[DataProvider('provideCreateDataCases')]
    #[DataProvider('provideOneTagInTextareaDataCases')]
    #[DataProvider('provideTwoTagsInTextareaDataCases')]
    #[DataProvider('provideCaseSensitiveTagsDataCases')]
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
