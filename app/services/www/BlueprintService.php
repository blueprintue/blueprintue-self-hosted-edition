<?php

declare(strict_types=1);

namespace app\services\www;

use app\helpers\Helper;
use app\models\BlueprintModel;
use app\models\BlueprintVersionModel;
use DateTime;
use DateTimeZone;
use Rancoud\Application\Application;

class BlueprintService
{
    /** @throws \Rancoud\Application\ApplicationException */
    public static function getBlueprintContent($fileID, $version): ?string
    {
        static $storageFolder = null;
        if ($storageFolder === null) {
            $storageFolder = Application::getFolder('STORAGE');
        }

        $caracters = \mb_str_split($fileID);
        $subfolder = '';
        foreach ($caracters as $c) {
            $subfolder .= $c . \DIRECTORY_SEPARATOR;
        }
        $subfolder = \mb_strtolower($subfolder);
        $fullpath = $storageFolder . $subfolder . $fileID . '-' . $version . '.txt';

        if (\file_exists($fullpath) === false) {
            return null;
        }

        return \file_get_contents($fullpath);
    }

    /** @throws \Rancoud\Application\ApplicationException */
    public static function setBlueprintContent($fileID, $version, $content): void
    {
        static $storageFolder = null;
        if ($storageFolder === null) {
            $storageFolder = Application::getFolder('STORAGE');
        }

        $caracters = \mb_str_split($fileID);
        $subfolder = '';
        foreach ($caracters as $c) {
            $subfolder .= $c . \DIRECTORY_SEPARATOR;
            static::createFolder($storageFolder, $subfolder);
        }
        $subfolder = \mb_strtolower($subfolder);
        $fullpath = $storageFolder . $subfolder . $fileID . '-' . $version . '.txt';

        \file_put_contents($fullpath, $content);
    }

    protected static function createFolder(string $storageFolder, string $subfolder): void
    {
        $dir = $storageFolder . $subfolder;
        if (!\is_dir($dir) && !\mkdir($dir) && !\is_dir($dir)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because folder are using only valid caraters
             * For covering we have to test setBlueprintContent only
             */
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function getLastFive(): ?array
    {
        $blueprints = (new BlueprintModel(Application::getDatabase()))->getLastFive();
        if ($blueprints === null) {
            return null;
        }

        foreach ($blueprints as $key => $blueprint) {
            $blueprints[$key]['thumbnail_url'] = Helper::getThumbnailUrl($blueprint['thumbnail']);
            $blueprints[$key]['url'] = Helper::getBlueprintLink($blueprint['slug']);
            $blueprints[$key]['since'] = Helper::getSince($blueprint['published_at']);
        }

        return $blueprints;
    }

    public static function isValidBlueprint(string $content): bool
    {
        $newBlueprintContent = \mb_strtolower($content);

        return !(\mb_strpos($newBlueprintContent, 'begin') !== 0);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function createFromHome(array $params): array
    {
        $errorCode = '#100';

        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintVersionModel = (new BlueprintVersionModel(Application::getDatabase()));

        $now = Helper::getNowUTCFormatted();

        $blueprintParams['title'] = $params['title'];
        $blueprintParams['exposure'] = $params['exposure'];
        $blueprintParams['expiration'] = static::computeExpiration($params['expiration'], $now);
        $blueprintParams['ue_version'] = $params['ue_version'];
        $blueprintParams['id_author'] = $params['id_author'];

        $blueprintParams['file_id'] = $blueprintParams['slug'] = static::getNewFileID($blueprintModel);
        $blueprintParams['current_version'] = 1;
        $blueprintParams['published_at'] = $blueprintParams['created_at'] = $now;
        $blueprintParams['type'] = static::findBlueprintType($params['blueprint']);

        $forceRollback = false;
        $blueprintID = 0;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $errorCode = '#200';
            $blueprintID = $blueprintModel->create($blueprintParams);
            if ($blueprintID === 0) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because blueprint requirements has been done before
                 * For covering we have to mock the database
                 */
                throw new \Exception('Blueprint ID is nil');
                // @codeCoverageIgnoreEnd
            }

            $errorCode = '#300';
            $blueprintVersionModel->create(
                [
                    'id_blueprint' => $blueprintID,
                    'version'      => $blueprintParams['current_version'],
                    'reason'       => 'First commit',
                    'created_at'   => $blueprintParams['created_at'],
                    'published_at' => $blueprintParams['published_at']
                ]
            );

            $errorCode = '#400';
            static::setBlueprintContent(
                $blueprintParams['file_id'],
                $blueprintParams['current_version'],
                $params['blueprint']
            );
        } catch (\Exception $exception) {
            $forceRollback = true;

            return [null, $errorCode];
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because blueprint requirements has been done before
                 * For covering we have to mock the database
                 */
                Application::getDatabase()->rollbackTransaction();
                // @codeCoverageIgnoreEnd
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return [['id' => $blueprintID, 'slug' => $blueprintParams['slug']], null];
    }

    public static function findBlueprintType(string $content): string
    {
        if (
            \mb_strpos($content, 'BehaviorTreeGraphNode_') !== false || \mb_strpos(
                $content,
                'BehaviorTreeDecoratorGraphNode_'
            ) !== false
        ) {
            return 'behavior_tree';
        }

        if (\mb_strpos($content, 'MaterialGraphNode') !== false) {
            return 'material';
        }

        if (\mb_strpos($content, 'AnimGraphNode_') !== false) {
            return 'animation';
        }

        if (\mb_strpos($content, '/Script/MetasoundEditor') !== false) {
            return 'metasound';
        }

        if (\mb_strpos($content, '/Script/NiagaraEditor') !== false) {
            return 'niagara';
        }

        if (\mb_strpos($content, 'PCGEditorGraphNode') !== false) {
            return 'pcg';
        }

        return 'blueprint';
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Database\DatabaseException
     */
    protected static function getNewFileID(BlueprintModel $blueprints): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz1234567890-_';
        $countCharacters = 37;
        $storageFolder = Application::getFolder('STORAGE');
        $attempts = 0;

        do {
            $fileID = '';
            $subfolder = '';

            for ($i = 0; $i < 8; ++$i) {
                $c = $characters[\random_int(0, $countCharacters)];
                $fileID .= $c;
                $subfolder .= $c . \DIRECTORY_SEPARATOR;
            }

            // check in database
            $fileIDAvailable = $blueprints->isNewFileIDAvailable($fileID);
            if ($fileIDAvailable) {
                // check in filesystem
                $fileIDAvailable = \count(\glob($storageFolder . $subfolder)) === 0;
            }

            if ($attempts > 50) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because filesystem is not filed with all folders
                 * For covering we have to mock the filesystem
                 */
                throw new \Exception('no more space');
                // @codeCoverageIgnoreEnd
            }

            ++$attempts;
        } while (!$fileIDAvailable);

        return $fileID;
    }

    /**
     * @param string $pageType [profile,last,most-discussed,type,tag,search]
     * @param array  $params   [page,count]
     *
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function search(string $pageType, ?int $connectedUserID, array $params = []): array
    {
        $results = ['rows' => null, 'count' => 0];
        $pagination = ['page' => (int) $params['page'], 'count' => (int) $params['count_per_page']];
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));

        if ($pageType === 'profile') {
            $showOnlyPublic = $connectedUserID !== $params['id_author'];
            $results = $blueprintModel->searchWithAuthor($params['id_author'], $showOnlyPublic, $pagination);
        } elseif ($pageType === 'last') {
            $results = $blueprintModel->searchLast($connectedUserID, $pagination);
        } elseif ($pageType === 'most-discussed') {
            $results = $blueprintModel->searchMostDiscussed($connectedUserID, $pagination);
        } elseif ($pageType === 'type') {
            $results = $blueprintModel->searchType($params['type'], $connectedUserID, $pagination);
        } elseif ($pageType === 'tag') {
            if ($params['tag'] !== null) {
                $results = $blueprintModel->searchTag((int) $params['tag']['id'], $connectedUserID, $pagination);
            }
        } elseif ($pageType === 'search') {
            $searchParams = [
                'query'      => $params['query'],
                'type'       => $params['type'],
                'ue_version' => $params['ue_version'],
            ];
            $results = $blueprintModel->search($searchParams, $connectedUserID, $pagination);
        }

        return $results;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getFromSlug(string $slug): ?array
    {
        return (new BlueprintModel(Application::getDatabase()))->getFromSlug($slug);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getAllVersions(int $blueprintID): ?array
    {
        return (new BlueprintVersionModel(Application::getDatabase()))->getAllVersions($blueprintID);
    }

    /** @throws \Exception */
    protected static function computeExpiration(string $expiration, string $now): ?string
    {
        $expirationDate = null;

        $delta = null;
        if ($expiration === '1h') {
            $delta = '+1 hour';
        } elseif ($expiration === '1d') {
            $delta = '+1 day';
        } elseif ($expiration === '1w') {
            $delta = '+1 week';
        }

        if ($delta !== null) {
            $expirationDate = (new DateTime($now, new DateTimeZone('UTC')))->modify($delta)->format('Y-m-d H:i:s');
        }

        return $expirationDate;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getForProfile(int $authorUserID, ?int $connectedUserID, int $page, int $countPerPage): array
    {
        $params = [
            'page'           => $page,
            'count_per_page' => $countPerPage,
            'id_author'      => $authorUserID
        ];

        return static::search('profile', $connectedUserID, $params);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function changeAuthor(int $fromID, int $toID): void
    {
        (new BlueprintModel(Application::getDatabase()))->changeAuthor($fromID, $toID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function softDeleteFromAuthor(int $id): void
    {
        (new BlueprintModel(Application::getDatabase()))->softDeleteFromAuthor($id);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function isAuthorBlueprint(int $blueprintID, int $userID): bool
    {
        return (new BlueprintModel(Application::getDatabase()))->isAuthorBlueprint($blueprintID, $userID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateThumbnail(int $id, ?string $filename): bool
    {
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprint = $blueprintModel->one($id);
        if (empty($blueprint)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because check on blueprint has been done
             * For covering we have to test service only
             */
            return false;
            // @codeCoverageIgnoreEnd
        }

        if ($blueprint['thumbnail'] !== null) {
            $filepathPreviousFile = Application::getFolder('MEDIAS_BLUEPRINTS') . $blueprint['thumbnail'];
            if (\preg_match('/^[a-zA-Z0-9]{60}\.png$/D', $blueprint['thumbnail']) === 1 && \file_exists($filepathPreviousFile) && \is_file($filepathPreviousFile)) {
                \unlink($filepathPreviousFile);
            }
        }

        $blueprintModel->update(['thumbnail' => $filename], $id);

        return true;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function claimBlueprint(int $blueprintID, int $userID): void
    {
        (new BlueprintModel(Application::getDatabase()))->update(['id_author' => $userID], $blueprintID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function deleteBlueprint(int $blueprintID): void
    {
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintVersionModel = (new BlueprintVersionModel(Application::getDatabase()));

        $blueprint = $blueprintModel->one($blueprintID);

        $storageFolder = Application::getFolder('STORAGE');
        $caracters = \mb_str_split($blueprint['file_id']);
        $subfolder = '';
        foreach ($caracters as $c) {
            $subfolder .= $c . \DIRECTORY_SEPARATOR;
        }
        $subfolder = \mb_strtolower($subfolder);
        foreach (\glob($storageFolder . $subfolder . $blueprint['file_id'] . '-*.txt') as $filepath) {
            if (\is_file($filepath)) {
                \unlink($filepath);
            }
        }

        $blueprintVersionModel->deleteWithBlueprintID($blueprintID);
        $blueprintModel->delete($blueprintID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function deleteVersion(int $blueprintID, int $version): ?int
    {
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintVersionModel = (new BlueprintVersionModel(Application::getDatabase()));
        $blueprint = $blueprintModel->one($blueprintID);
        $versions = $blueprintVersionModel->getAllVersions($blueprintID);
        if (\count($versions) === 1) {
            return -1;
        }

        foreach ($versions as $k => $v) {
            if ($v['version'] === $version) {
                $blueprintVersionModel->deleteWithBlueprintIDAndVersion($blueprintID, $version);
                if ($blueprint['current_version'] === $version) {
                    $idx = 0;
                    if ($k === 0) {
                        // if last version is delete we have to take the previous one
                        $idx = 1;
                    }
                    $blueprintModel->update(['current_version' => $versions[$idx]['version']], $blueprintID);
                }

                $storageFolder = Application::getFolder('STORAGE');
                $caracters = \mb_str_split($blueprint['file_id']);
                $subfolder = '';
                foreach ($caracters as $c) {
                    $subfolder .= $c . \DIRECTORY_SEPARATOR;
                }
                $subfolder = \mb_strtolower($subfolder);
                $filepath = $storageFolder . $subfolder . $blueprint['file_id'] . '-' . $version . '.txt';
                if (\file_exists($filepath) && \is_file($filepath)) {
                    \unlink($filepath);
                }

                return null;
            }
        }

        return -2;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function changeBlueprintAuthor(int $blueprintID, ?int $userID): void
    {
        (new BlueprintModel(Application::getDatabase()))->update(['id_author' => $userID], $blueprintID);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function softDeleteBlueprint(int $blueprintID): void
    {
        (new BlueprintModel(Application::getDatabase()))->update(['deleted_at' => Helper::getNowUTCFormatted()], $blueprintID);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function addVersion(int $blueprintID, string $blueprint, string $reason): ?string
    {
        $errorCode = '#100';

        $now = Helper::getNowUTCFormatted();

        $forceRollback = false;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $blueprintModel = (new BlueprintModel(Application::getDatabase()));
            $blueprintInfos = $blueprintModel->one($blueprintID);
            if ($blueprintInfos === null) {
                // @codeCoverageIgnoreStart
                $forceRollback = true;

                throw new \Exception('Blueprint is nil');
                // @codeCoverageIgnoreEnd
            }

            $errorCode = '#200';
            $blueprintVersionModel = (new BlueprintVersionModel(Application::getDatabase()));
            $nextVersion = $blueprintVersionModel->getNextVersion($blueprintID);

            $errorCode = '#300';
            $blueprintVersionModel->create(
                [
                    'id_blueprint' => $blueprintID,
                    'version'      => $nextVersion,
                    'reason'       => $reason,
                    'created_at'   => $now,
                    'published_at' => $now,
                ]
            );

            $errorCode = '#400';
            static::setBlueprintContent(
                $blueprintInfos['file_id'],
                $nextVersion,
                $blueprint
            );

            $errorCode = '#500';
            $blueprintModel->update(
                [
                    'current_version' => $nextVersion,
                    'updated_at'      => $now
                ],
                $blueprintID
            );

            $errorCode = null;
        } catch (\Exception $exception) {
            return $errorCode;
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                // @codeCoverageIgnoreStart
                Application::getDatabase()->rollbackTransaction();
                // @codeCoverageIgnoreEnd
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return $errorCode;
    }

    /**
     * @param array $params [exposure,expiration,ue_version,comments_hidden,comments_closed]
     *
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateProperties(int $blueprintID, array $params): void
    {
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintModel->update(
            [
                'exposure'        => $params['exposure'],
                'expiration'      => $params['expiration'],
                'ue_version'      => $params['ue_version'],
                'comments_hidden' => $params['comments_hidden'],
                'comments_closed' => $params['comments_closed'],
                'updated_at'      => Helper::getNowUTCFormatted(),
            ],
            $blueprintID
        );
    }

    /**
     * @param array $params [title,description,video_url]
     *
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function updateInformations(int $blueprintID, array $params): void
    {
        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintModel->update(
            [
                'title'          => $params['title'],
                'description'    => $params['description'],
                'tags'           => $params['tags'],
                'video'          => $params['video'],
                'video_provider' => $params['video_provider'],
                'updated_at'     => Helper::getNowUTCFormatted(),
            ],
            $blueprintID
        );
    }

    /** @return array [video,video_provider] */
    public static function findVideoProvider(string $videoURL): array
    {
        if ($videoURL === '') {
            return [null, null];
        }

        $providersMatcher = [
            [
                'provider' => 'youtube',
                'regex'    => '/(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\/?\?v=|watch\/?\?.+&v=))([A-Za-z0-9_-]{11})/i',
                'output'   => '//www.youtube.com/embed/{{1}}',
            ],
            [
                'provider' => 'youtube',
                'regex'    => '/(?:https?:\/\/)?(?:www\.)?youtube-nocookie\.com\/embed\/([A-Za-z0-9_-]{11})/i',
                'output'   => '//www.youtube.com/embed/{{1}}',
            ],
            [
                'provider' => 'vimeo',
                'regex'    => '/(?:https?:\/\/)?(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|showcase\/(?:[^\/]+)\/video\/|video\/|)(\d+)/i',
                'output'   => '//player.vimeo.com/video/{{1}}',
            ],
            [
                'provider' => 'dailymotion',
                'regex'    => '/(?:https?:\/\/)?(?:api\.dailymotion\.com|www\.dailymotion\.com)\/(?:video|hub)\/([^#?&\/]+)?/i',
                'output'   => '//www.dailymotion.com/embed/video/{{1}}',
            ],
            [
                'provider' => 'dailymotion',
                'regex'    => '/(?:https?:\/\/)?dai\.ly\/([^#?&\/]+)?/i',
                'output'   => '//www.dailymotion.com/embed/video/{{1}}',
            ],
            [
                'provider' => 'dailymotion',
                'regex'    => '/(?:https?:\/\/)?(?:www\.dailymotion\.com)\/embed\/video\/([^#?&\/]+)?/i',
                'output'   => '//www.dailymotion.com/embed/video/{{1}}',
            ],
            [
                'provider' => 'bilibili',
                'regex'    => '/(?:https?:\/\/)?(?:www\.)?bilibili\.com\/video\/av(\d+)/i',
                'output'   => '//player.bilibili.com/player.html?aid={{1}}',
            ],
            [
                'provider' => 'bilibili',
                'regex'    => '/(?:https?:\/\/)?player\.bilibili\.com\/player\.html\?aid=(\d+)/i',
                'output'   => '//player.bilibili.com/player.html?aid={{1}}',
            ],
            [
                'provider' => 'niconico',
                'regex'    => '/(?:https?:\/\/)?(?:www\.)?nicovideo\.jp\/watch\/sm(\d+)/i',
                'output'   => '//embed.nicovideo.jp/watch/sm{{1}}',
            ],
            [
                'provider' => 'peertube',
                'regex'    => '/(?:https?:\/\/)?([^\/]+)\/videos\/(?:watch|embed)\/([a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12})(?:[^#?&\/]+)?/i',
                'output'   => '//{{1}}/videos/embed/{{2}}',
            ]
        ];

        foreach ($providersMatcher as $providerMatcher) {
            if (\preg_match($providerMatcher['regex'], $videoURL, $matches) === 1) {
                $output = \str_replace('{{1}}', $matches[1], $providerMatcher['output']);
                if ($providerMatcher['provider'] === 'peertube') {
                    $output = \str_replace('{{2}}', $matches[2], $output);
                }

                return [$output, $providerMatcher['provider']];
            }
        }

        return [null, null];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function updateCommentCount(int $blueprintID, int $count): void
    {
        (new BlueprintModel(Application::getDatabase()))->updateCommentCount($blueprintID, $count);
    }

    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function createFromAPI(array $params): array
    {
        $errorCode = '#100';

        $blueprintModel = (new BlueprintModel(Application::getDatabase()));
        $blueprintVersionModel = (new BlueprintVersionModel(Application::getDatabase()));

        $now = Helper::getNowUTCFormatted();

        $blueprintParams['title'] = $params['title'];
        $blueprintParams['exposure'] = $params['exposure'];
        $blueprintParams['expiration'] = static::computeExpiration($params['expiration'], $now);
        $blueprintParams['ue_version'] = $params['ue_version'];
        $blueprintParams['id_author'] = $params['id_author'];

        $blueprintParams['file_id'] = $blueprintParams['slug'] = static::getNewFileID($blueprintModel);
        $blueprintParams['current_version'] = 1;
        $blueprintParams['published_at'] = $blueprintParams['created_at'] = $now;
        $blueprintParams['type'] = static::findBlueprintType($params['blueprint']);

        $forceRollback = false;
        $blueprintID = 0;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            $errorCode = '#200';
            $blueprintID = $blueprintModel->create($blueprintParams);
            if ($blueprintID === 0) {
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because blueprint requirements has been done before
                 * For covering we have to mock the database
                 */
                throw new \Exception('Blueprint ID is nil');
                // @codeCoverageIgnoreEnd
            }

            $errorCode = '#300';
            $blueprintVersionModel->create(
                [
                    'id_blueprint' => $blueprintID,
                    'version'      => $blueprintParams['current_version'],
                    'reason'       => 'First commit',
                    'created_at'   => $blueprintParams['created_at'],
                    'published_at' => $blueprintParams['published_at']
                ]
            );

            $errorCode = '#400';
            static::setBlueprintContent(
                $blueprintParams['file_id'],
                $blueprintParams['current_version'],
                $params['blueprint']
            );
        } catch (\Exception $exception) {
            $forceRollback = true;

            return [null, $errorCode];
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                // @codeCoverageIgnoreStart
                /*
                 * In end 2 end testing we can't arrive here because blueprint requirements has been done before
                 * For covering we have to mock the database
                 */
                Application::getDatabase()->rollbackTransaction();
                // @codeCoverageIgnoreEnd
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }

        return [['id' => $blueprintID, 'slug' => $blueprintParams['slug']], null];
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function getTagsFromPublicBlueprints(?int $connectedUserID): array
    {
        $tagsIDs = [];

        $rawTagIDS = (new BlueprintModel(Application::getDatabase()))->getTagsFromPublicBlueprints($connectedUserID);

        foreach ($rawTagIDS as $rawTags) {
            $ids = \explode(',', $rawTags);
            foreach ($ids as $id) {
                $id = (int) $id;
                if (!\in_array($id, $tagsIDs, true)) {
                    $tagsIDs[] = $id;
                }
            }
        }

        return $tagsIDs;
    }
}
