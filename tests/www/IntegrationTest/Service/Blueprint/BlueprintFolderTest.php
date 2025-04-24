<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use PHPUnit\Framework\TestCase;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Router\RouterException;
use Rancoud\Session\Session;
use tests\Common;

class BlueprintFolderTest extends TestCase
{
    use Common;

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    /**
     * @throws RouterException
     * @throws EnvironmentException
     * @throws ApplicationException
     * @throws DatabaseException
     */
    protected function setUp(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }

        static::setDatabaseEmptyStructure();

        $ds = \DIRECTORY_SEPARATOR;

        $this->getResponseFromApplication('GET', '/');
        $dir = Application::getFolder('STORAGE');

        $possibilities = [['a', 'a'], ['a', 'b'], ['b', 'b'], ['b', 'a']];
        foreach ($possibilities as $possibility) {
            foreach (\glob($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . '*.txt') as $filepath) {
                if (\is_file($filepath)) {
                    \unlink($filepath);
                }
            }

            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds . 'c' . $ds);
            }
            if (\is_dir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds)) {
                \rmdir($dir . $possibility[0] . $ds . $possibility[1] . $ds . 'c' . $ds);
            }
        }
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    public function testGenerateFileIDDatabase(): void
    {
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnDatabase());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnDatabase());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnDatabase());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnDatabase());

        $exceptionLaunched = false;

        try {
            ExtendedBlueprintService::testNewFileIDOnDatabase();
        } catch (\Exception $e) {
            $exceptionLaunched = true;
        }

        static::assertTrue($exceptionLaunched);
        static::assertSame(4, Application::getDatabase()->count('SELECT COUNT(*) FROM blueprints'));
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    public function testGenerateFileIDFileSystem(): void
    {
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnFileSystem());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnFileSystem());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnFileSystem());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDOnFileSystem());

        $exceptionLaunched = false;

        try {
            ExtendedBlueprintService::testNewFileIDOnFileSystem();
        } catch (\Exception $e) {
            $exceptionLaunched = true;
        }

        static::assertTrue($exceptionLaunched);
        static::assertSame(0, Application::getDatabase()->count('SELECT COUNT(*) FROM blueprints'));
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    public function testGenerateFileIDBoth(): void
    {
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDBoth());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDBoth());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDBoth());
        static::assertNotSame('', ExtendedBlueprintService::testNewFileIDBoth());

        $exceptionLaunched = false;

        try {
            ExtendedBlueprintService::testNewFileIDBoth();
        } catch (\Exception $e) {
            $exceptionLaunched = true;
        }

        static::assertTrue($exceptionLaunched);
        static::assertSame(4, Application::getDatabase()->count('SELECT COUNT(*) FROM blueprints'));
    }
}
