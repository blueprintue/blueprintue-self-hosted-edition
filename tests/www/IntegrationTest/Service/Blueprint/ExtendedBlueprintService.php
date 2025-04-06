<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\models\BlueprintModel;
use app\services\www\BlueprintService;
use DateTime;
use DateTimeZone;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;

class ExtendedBlueprintService extends BlueprintService
{
    /**
     * @throws DatabaseException
     * @throws \Exception
     */
    protected static function getNewFileID(BlueprintModel $blueprints): string
    {
        $characters = 'ab';
        $countCharacters = 1;
        $storageFolder = Application::getFolder('STORAGE');
        $attempts = 0;

        do {
            $fileID = '';
            $subfolder = '';

            for ($i = 0; $i < 2; ++$i) {
                $c = $characters[\random_int(0, $countCharacters)];
                $fileID .= $c;
                $subfolder .= $c . '/';
            }
            $fileID .= 'cccccccc';
            $subfolder .= 'c/c/c/c/c/c/c/c/';

            // check in database
            $fileIDAvailable = $blueprints->isNewFileIDAvailable($fileID);
            if ($fileIDAvailable) {
                // check in filesystem
                $fileIDAvailable = \count(\glob($storageFolder . $subfolder)) === 0;
            }

            if ($attempts > 50) {
                throw new \Exception('no more space');
            }

            ++$attempts;
        } while (!$fileIDAvailable);

        return $fileID;
    }

    /**
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws \Exception
     */
    public static function testNewFileIDOnDatabase(): string
    {
        $blueprintModel = new BlueprintModel(Application::getDatabase());
        $fileID = static::getNewFileID($blueprintModel);
        $blueprintModel->create([
            'id_author'       => 1,
            'slug'            => $fileID,
            'file_id'         => $fileID,
            'title'           => $fileID,
            'type'            => 'blueprint',
            'ue_version'      => '4.25',
            'current_version' => 1,
            'exposure'        => 'public',
            'created_at'      => (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
        ]);

        return $fileID;
    }

    /**
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws \Exception
     */
    public static function testNewFileIDOnFileSystem(): string
    {
        $blueprintModel = new BlueprintModel(Application::getDatabase());
        $fileID = static::getNewFileID($blueprintModel);
        static::setBlueprintContent($fileID, '1', 'aze');

        return $fileID;
    }

    /**
     * @throws DatabaseException
     * @throws ApplicationException
     * @throws \Exception
     */
    public static function testNewFileIDBoth(): string
    {
        $blueprintModel = new BlueprintModel(Application::getDatabase());
        $fileID = static::getNewFileID($blueprintModel);
        $blueprintModel->create([
            'id_author'       => 1,
            'slug'            => $fileID,
            'file_id'         => $fileID,
            'title'           => $fileID,
            'type'            => 'blueprint',
            'ue_version'      => '4.25',
            'current_version' => 1,
            'exposure'        => 'public',
            'created_at'      => (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'),
        ]);

        static::setBlueprintContent($fileID, '1', 'aze');

        return $fileID;
    }
}
