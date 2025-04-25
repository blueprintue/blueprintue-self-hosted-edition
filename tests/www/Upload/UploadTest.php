<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\Upload;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rancoud\Database\DatabaseException;
use Rancoud\Http\Message\UploadedFile;
use Rancoud\Session\Session;
use tests\Common;

/** @internal */
class UploadTest extends TestCase
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
            'id'       => 129,
            'username' => 'user_129',
            'hash'     => null,
            'slug'     => 'user_129',
            'email'    => 'user_129@example.com',
            'grade'    => 'member',
            'avatar'   => null,
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 139,
            'username' => 'user_139',
            'hash'     => null,
            'slug'     => 'user_139',
            'email'    => 'user_139@example.com',
            'grade'    => 'member',
            'avatar'   => 'fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        $userParams = [
            'id'       => 149,
            'username' => 'user_149 <script>alert(1)</script>',
            'hash'     => null,
            'slug'     => 'user_149\"><script>alert(1)</script>',
            'email'    => 'user_149@example.com',
            'grade'    => 'member',
            'avatar'   => 'mem\"><script>alert(1)</script>fromage.jpg'
        ];
        static::$db->insert($sql, $userParams);

        foreach (\glob(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias' . \DIRECTORY_SEPARATOR . '*.png') as $filepath) {
            \unlink($filepath);
        }

        // blueprint generation
        $sql = <<<'SQL'
            INSERT INTO `blueprints` (`id`, `id_author`, `slug`, `file_id`, `title`, `type`, `ue_version`, `current_version`, `thumbnail`, `created_at`, `exposure`)
                VALUES (:id, :id_author, :slug, :file_id, :title, :type, :ue_version, :current_version, :thumbnail, UTC_TIMESTAMP(), :exposure);
        SQL;

        $blueprintParams = [
            'id'              => 965,
            'id_author'       => 129,
            'slug'            => '9ov193uu',
            'file_id'         => '9ov193uu',
            'title'           => 'title',
            'type'            => 'blueprint',
            'ue_version'      => '4.26',
            'current_version' => 1,
            'thumbnail'       => null,
            'exposure'        => 'private'
        ];
        static::$db->insert($sql, $blueprintParams);

        $blueprintParams = [
            'id'              => 975,
            'id_author'       => 129,
            'slug'            => '97v193uu',
            'file_id'         => '97v193uu',
            'title'           => 'title',
            'type'            => 'blueprint',
            'ue_version'      => '4.26',
            'current_version' => 1,
            'thumbnail'       => 'fromage.jpg',
            'exposure'        => 'private'
        ];
        static::$db->insert($sql, $blueprintParams);

        $blueprintParams = [
            'id'              => 985,
            'id_author'       => 129,
            'slug'            => '98v193uu',
            'file_id'         => '98v193uu',
            'title'           => 'title',
            'type'            => 'blueprint',
            'ue_version'      => '4.26',
            'current_version' => 1,
            'thumbnail'       => 'mem\"><script>alert(1)</script>fromage.jpg',
            'exposure'        => 'private'
        ];
        static::$db->insert($sql, $blueprintParams);
    }

    protected function tearDown(): void
    {
        if (Session::isReadOnly() === false) {
            Session::commit();
        }
    }

    public static function provideURL404DataCases(): iterable
    {
        return [
            'invalid url - user/thumbnail' => [
                'slug'               => '/upload/user/1/thumbnail/',
                'statusCode'         => 404,
                'responseContent'    => '',
                'userID'             => null,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'invalid url - blueprint/avatar' => [
                'slug'               => '/upload/blueprint/1/avatar/',
                'statusCode'         => 404,
                'responseContent'    => '',
                'userID'             => null,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ]
        ];
    }

    public static function provideUserAvatarDataCases(): iterable
    {
        $folderUploadedFiles = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'uploaded_files' . \DIRECTORY_SEPARATOR;
        $files = [
            'no tmp file'                 => new UploadedFile('no_tmp_file', 0, \UPLOAD_ERR_OK, 'avatar'),
            'invalid file - upload error' => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_PARTIAL, 'avatar', 'image/png'),
            'invalid file - invalid type' => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_OK, 'avatar', 'invalid/file'),
            'invalid file - invalid size' => new UploadedFile('no_tmp_file', 0, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
            'invalid file - not found'    => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
            'invalid file - fake image'   => new UploadedFile($folderUploadedFiles . 'invalid_file.png', 1, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
            '1x1'                         => new UploadedFile($folderUploadedFiles . '1x1.png', 1, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
            '310x310'                     => new UploadedFile($folderUploadedFiles . '310x310.png', 1, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
            'corrupt imagecreatefrompng'  => new UploadedFile($folderUploadedFiles . 'corrupt_imagecreatefrompng.tmp', 1, \UPLOAD_ERR_OK, 'avatar', 'image/png'),
        ];

        return [
            'user not logged - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"user not logged"}',
                'userID'             => null,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'entity id is not same as user logged - user/avatar' => [
                'slug'               => '/upload/user/9/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"user forbidden"}',
                'userID'             => 1,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'folder not initialize in Application - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"folder for avatars not found"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['310x310'],
                ],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'missing all params - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params canvas_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params canvas_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_x - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_y - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params canvas_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => -1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params canvas_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 0, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => -1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 0, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_x - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => -1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_y - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 0],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params csrf - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params csrf - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1, 'csrf' => 'incorrect_csrf'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params canvas_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params canvas_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 1, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_width - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 1, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_height - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 1, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_x - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 1, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_y - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 1, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing file - user/avatar' => [
                'slug'               => '/upload/user/129/avatar/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"missing file"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - missing type - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['no tmp file'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - invalid type - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['invalid file - invalid type'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - invalid size - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['invalid file - invalid size'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - error upload - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['invalid file - upload error'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - no file found - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['invalid file - not found'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch file and header - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['invalid file - fake image'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas width params (file invalid) - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas height params (file invalid) - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas params (file invalid) - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            /*'could not use image - imagecreatefrompng - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"could not use image"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['corrupt imagecreatefrompng'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],*/
            'could not update user avatar - user not found - user/avatar' => [
                'slug'            => '/upload/user/1/avatar/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"could not update user avatar"}',
                'userID'          => 1,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'update OK - user with no avatar before - user/avatar' => [
                'slug'            => '/upload/user/129/avatar/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
            'update OK - user with avatar before - user/avatar' => [
                'slug'            => '/upload/user/139/avatar/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 139,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
            'update OK - user with avatar exploit before - user/avatar' => [
                'slug'            => '/upload/user/149/avatar/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 149,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'avatar' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_AVATARS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
        ];
    }

    public static function provideBlueprintThumbnailDataCases(): iterable
    {
        $folderUploadedFiles = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'uploaded_files' . \DIRECTORY_SEPARATOR;
        $files = [
            'no tmp file'                 => new UploadedFile('no_tmp_file', 0, \UPLOAD_ERR_OK, 'thumbnail'),
            'invalid file - upload error' => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_PARTIAL, 'thumbnail', 'image/png'),
            'invalid file - invalid type' => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_OK, 'thumbnail', 'invalid/file'),
            'invalid file - invalid size' => new UploadedFile('no_tmp_file', 0, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
            'invalid file - not found'    => new UploadedFile('no_tmp_file', 1, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
            'invalid file - fake image'   => new UploadedFile($folderUploadedFiles . 'invalid_file.png', 1, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
            '1x1'                         => new UploadedFile($folderUploadedFiles . '1x1.png', 1, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
            '310x310'                     => new UploadedFile($folderUploadedFiles . '310x310.png', 1, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
            'corrupt imagecreatefrompng'  => new UploadedFile($folderUploadedFiles . 'corrupt_imagecreatefrompng.tmp', 1, \UPLOAD_ERR_OK, 'thumbnail', 'image/png'),
        ];

        return [
            'user not logged - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"user not logged"}',
                'userID'             => null,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'blueprint id is not valid bluepint - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/1/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"user not author"}',
                'userID'             => 1,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'blueprint id is not owned by the user logged - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"user not author"}',
                'userID'             => 1,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'folder not initialize in Application - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"folder for thumbnails not found"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['310x310'],
                ],
                'additionalsFolders' => [],
                'isUploaded'         => false,
            ],
            'missing all params - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => [],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params canvas_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params canvas_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_height' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_x' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_x - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_y' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params mask_y - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => '', 'canvas_height' => '', 'mask_width' => '', 'mask_height' => '', 'mask_x' => ''],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params canvas_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => -1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params canvas_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 0, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => -1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 0, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_x - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => -1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params mask_y - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 0],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing params csrf - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid value in params csrf - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 1, 'mask_width' => 1, 'mask_height' => 1, 'mask_x' => 1, 'mask_y' => 1, 'csrf' => 'incorrect_csrf'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params canvas_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 1, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params canvas_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 1, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_width - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 1, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_height - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 1, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_x - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 1, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid constraint value in params mask_y - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"invalid constraints parameters"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 1, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'missing file - blueprint/thumbnail' => [
                'slug'               => '/upload/blueprint/965/thumbnail/',
                'statusCode'         => 400,
                'responseContent'    => '{"message":"missing file"}',
                'userID'             => 129,
                'params'             => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'               => [],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - missing type - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['no tmp file'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - invalid type - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['invalid file - invalid type'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - invalid size - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['invalid file - invalid size'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - error upload - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['invalid file - upload error'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - no file found - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['invalid file - not found'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch file and header - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['invalid file - fake image'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas width params (file invalid) - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas height params (file invalid) - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            'invalid file - mismatch size file and canvas params (file invalid) - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"invalid file"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['1x1'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],
            /*'could not use image - imagecreatefrompng - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 400,
                'responseContent' => '{"message":"could not use image"}',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['corrupt imagecreatefrompng'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => false,
            ],*/
            'update OK - blueprint with no thumbnail before - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/965/thumbnail/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
            'update OK - blueprint with thumbnail before - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/975/thumbnail/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
            'update OK - blueprint with thumbnail exploit before - blueprint/thumbnail' => [
                'slug'            => '/upload/blueprint/985/thumbnail/',
                'statusCode'      => 200,
                'responseContent' => '',
                'userID'          => 129,
                'params'          => ['canvas_width' => 310, 'canvas_height' => 310, 'mask_width' => 200, 'mask_height' => 200, 'mask_x' => 55, 'mask_y' => 55, 'csrf' => 'csrf_is_replaced'],
                'file'            => [
                    'thumbnail' => $files['310x310'],
                ],
                'additionalsFolders' => ['MEDIAS_BLUEPRINTS' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'medias'],
                'isUploaded'         => true,
            ],
        ];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     * @throws \Rancoud\Router\RouterException
     * @throws DatabaseException
     */
    #[DataProvider('provideURL404DataCases')]
    #[DataProvider('provideUserAvatarDataCases')]
    #[DataProvider('provideBlueprintThumbnailDataCases')]
    public function testUpload(string $slug, int $statusCode, string $responseContent, ?int $userID, array $params, array $file, array $additionalsFolders, bool $isUploaded): void
    {
        $sessionValues = [
            'set'    => [],
            'remove' => ['userID']
        ];

        $tmpPath = null;

        if ($userID !== null) {
            $sessionValues = [
                'set'    => ['userID' => $userID],
                'remove' => []
            ];

            $tmpFile = \tmpfile();
            $tmpPath = \stream_get_meta_data($tmpFile)['uri'];
            if (\file_exists($tmpPath)) {
                if (\mb_strpos($slug, 'avatar') !== false) {
                    $_FILES['avatar']['tmp_name'] = $tmpPath;
                } else {
                    $_FILES['thumbnail']['tmp_name'] = $tmpPath;
                }
            }
        }

        $this->getResponseFromApplication('GET', '/', [], $sessionValues);

        $folder = $additionalsFolders['MEDIAS_AVATARS'] ?? $additionalsFolders['MEDIAS_BLUEPRINTS'] ?? '';
        $userAvatarBefore = null;
        $blueprintThumbnailBefore = null;
        if ($isUploaded) {
            if (isset($additionalsFolders['MEDIAS_AVATARS'])) {
                $userAvatarBefore = static::$db->selectVar('SELECT avatar FROM users WHERE id = :user_id', ['user_id' => $userID]);
                if (($userAvatarBefore !== null) && \preg_match('/^[a-zA-Z0-9]{60}\.png$/D', $userAvatarBefore) === 1) {
                    \file_put_contents($folder . \DIRECTORY_SEPARATOR . $userAvatarBefore, 'aaa');
                }
            } else {
                $blueprintThumbnailBefore = static::$db->selectVar('SELECT thumbnail FROM blueprints WHERE id = :id', ['id' => \str_replace(['/upload/blueprint/', '/thumbnail/'], ['', ''], $slug)]);
                if (($blueprintThumbnailBefore !== null) && \preg_match('/^[a-zA-Z0-9]{60}\.png$/D', $blueprintThumbnailBefore) === 1) {
                    \file_put_contents($folder . \DIRECTORY_SEPARATOR . $blueprintThumbnailBefore, 'aaa');
                }
            }
        }

        if (isset($params['csrf']) && $params['csrf'] === 'csrf_is_replaced') {
            $params['csrf'] = $_SESSION['csrf'];
        }

        $response = $this->getResponseFromApplication('POST', $slug, $params, [], [], [], $file, $additionalsFolders);

        if ($tmpPath !== null) {
            static::assertFileDoesNotExist($tmpPath);
        }

        $this->doTestHasResponseWithStatusCode($response, $statusCode);
        if ($isUploaded === false) {
            static::assertSame((string) $response->getBody(), $responseContent);
        } elseif (isset($additionalsFolders['MEDIAS_AVATARS'])) {
            $userAvatarAfter = static::$db->selectVar('SELECT avatar FROM users WHERE id = :user_id', ['user_id' => $userID]);
            if ($userAvatarBefore !== null) {
                static::assertFileDoesNotExist($folder . \DIRECTORY_SEPARATOR . $userAvatarBefore);
            }

            static::assertSame((string) $response->getBody(), '{"file_url":"\/medias\/avatars\/' . $userAvatarAfter . '"}');
            static::assertFileExists($folder . \DIRECTORY_SEPARATOR . $userAvatarAfter);
        } else {
            $blueprintThumbnailAfter = static::$db->selectVar('SELECT thumbnail FROM blueprints WHERE id = :id', ['id' => \str_replace(['/upload/blueprint/', '/thumbnail/'], ['', ''], $slug)]);
            if ($blueprintThumbnailBefore !== null) {
                static::assertFileDoesNotExist($folder . \DIRECTORY_SEPARATOR . $blueprintThumbnailBefore);
            }

            static::assertSame((string) $response->getBody(), '{"file_url":"\/medias\/blueprints\/' . $blueprintThumbnailAfter . '"}');
            static::assertFileExists($folder . \DIRECTORY_SEPARATOR . $blueprintThumbnailAfter);
        }
    }
}
