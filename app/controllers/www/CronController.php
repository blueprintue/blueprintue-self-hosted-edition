<?php

declare(strict_types=1);

namespace app\controllers\www;

use app\services\www\BlueprintService;
use app\services\www\CommentService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rancoud\Application\Application;
use Rancoud\Application\ApplicationException;
use Rancoud\Database\DatabaseException;
use Rancoud\Environment\EnvironmentException;
use Rancoud\Http\Message\Factory\Factory;
use Rancoud\Session\Session;

class CronController
{
    /** @throws \Exception */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @noinspection NullPointerExceptionInspection */
        $currentRoute = Application::getRouter()->getCurrentRoute()->getCallback()->getCurrentRoute()->getName();
        switch ($currentRoute) {
            case 'cron_purge_sessions':
                $this->purgeSessions();

                break;
            case 'cron_purge_users_not_confirmed':
                $this->purgeUsersNotConfirmed();

                break;
            case 'cron_purge_deleted_blueprints':
                $this->purgeDeletedBlueprints();

                break;
            case 'cron_set_soft_delete_anonymous_private_blueprints':
                $this->setSoftDeleteAnonymousPrivateBlueprints();

                break;
        }

        return (new Factory())->createResponse(204);
    }

    /**
     * @throws ApplicationException
     * @throws EnvironmentException
     */
    protected function purgeSessions(): void
    {
        $sessionGCMaxlifetime = (int) Application::getConfig()->get('SESSION_GC_MAXLIFETIME', 86400);
        Session::getDriver()->gc($sessionGCMaxlifetime);
        Session::destroy();
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    protected function purgeUsersNotConfirmed(): void
    {
        $forceRollback = false;
        $sqlSelected = <<<'SQL'
            SELECT id
            FROM users
            WHERE users.created_at IS NOT NULL
                AND users.confirmed_at IS NULL
                AND users.created_at < NOW() - INTERVAL 30 DAY
        SQL;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            /** @noinspection NullPointerExceptionInspection */
            $userIDs = Application::getDatabase()->selectCol($sqlSelected);
            foreach ($userIDs as $key => $value) {
                $userIDs[$key] = (int) $value;
            }

            $inStr = \implode(',', \array_unique($userIDs));

            $sqlDeleteUsers = <<<SQL
                DELETE FROM users
                WHERE id IN ({$inStr});
            SQL;

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->delete($sqlDeleteUsers);
            $sqlDeleteUsersInfos = <<<SQL
                DELETE FROM users_infos
                WHERE id_user IN ({$inStr});
            SQL;

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->delete($sqlDeleteUsersInfos);
        } catch (\Exception $e) {
            $forceRollback = true;
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    protected function purgeDeletedBlueprints(): void
    {
        $forceRollback = false;
        $sqlSelected = <<<'SQL'
            SELECT id, id_author
            FROM blueprints
            WHERE deleted_at IS NOT NULL
                OR (expiration IS NOT NULL AND expiration < UTC_TIMESTAMP())
            LIMIT 10
        SQL;
        $userIDsToCompute = [];

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            /** @noinspection NullPointerExceptionInspection */
            $blueprints = Application::getDatabase()->selectAll($sqlSelected);
            foreach ($blueprints as $blueprint) {
                $blueprintID = (int) $blueprint['id'];
                $authorID = (int) $blueprint['id_author'];
                $userIDsToCompute[] = $authorID;

                $comments = CommentService::getAllCommentsWithBlueprintID($blueprintID);
                if ($comments !== null) {
                    foreach ($comments as $comment) {
                        $userIDsToCompute[] = (int) $comment['id_author'];
                    }
                }

                BlueprintService::deleteBlueprint($blueprintID);
                CommentService::deleteCommentsWithBlueprintID($blueprintID);
            }

            $userIDsToCompute = \array_unique($userIDsToCompute);
            foreach ($userIDsToCompute as $userIDToCompute) {
                $this->updateUserCounters($userIDToCompute);
            }
        } catch (\Exception $e) {
            $forceRollback = true;
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     */
    protected function updateUserCounters(int $userID): void
    {
        // @codeCoverageIgnoreStart
        /*
         * In end 2 end testing we can't arrive here because user requirements has been done before
         * For covering we have to test the function outside
         */
        if ($userID === 0) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $paramsSetUserCounters = [
            'userID'                => $userID,
            'countPublicBlueprint'  => 0,
            'countPrivateBlueprint' => 0,
            'countPublicComment'    => 0,
            'countPrivateComment'   => 0
        ];

        $sqlPublicBlueprints = <<<'SQL'
            SELECT COUNT(*)
            FROM blueprints
            WHERE id_author = :userID
              AND published_at IS NOT NULL
              AND deleted_at IS NULL
              AND exposure = 'public'
              AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
        SQL;

        $sqlPrivateBlueprints = <<<'SQL'
            SELECT COUNT(*)
            FROM blueprints
            WHERE id_author = :userID
              AND published_at IS NOT NULL
              AND deleted_at IS NULL
              AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
        SQL;

        $sqlPublicComments = <<<'SQL'
            SELECT COUNT(*)
            FROM comments AS c
            INNER JOIN blueprints AS b ON c.id_blueprint = b.id
            WHERE c.id_author = :userID
              AND b.published_at IS NOT NULL
              AND b.deleted_at IS NULL
              AND b.exposure = 'public'
              AND (b.expiration IS NULL OR b.expiration > UTC_TIMESTAMP())
        SQL;

        $sqlPrivateComments = <<<'SQL'
            SELECT COUNT(*)
            FROM comments AS c
            INNER JOIN blueprints AS b ON c.id_blueprint = b.id
            WHERE c.id_author = :userID
              AND b.published_at IS NOT NULL
              AND b.deleted_at IS NULL
              AND (b.expiration IS NULL OR b.expiration > UTC_TIMESTAMP())
        SQL;

        $sqlSetUserCounters = <<<'SQL'
            UPDATE users_infos
            SET count_public_blueprint = :countPublicBlueprint,
                count_private_blueprint = :countPrivateBlueprint,
                count_private_comment = :countPrivateComment,
                count_public_comment = :countPublicComment
            WHERE id_user = :userID
        SQL;

        /* @noinspection NullPointerExceptionInspection */
        $paramsSetUserCounters['countPublicBlueprint'] = Application::getDatabase()->count(
            $sqlPublicBlueprints,
            ['userID' => $userID]
        );

        /* @noinspection NullPointerExceptionInspection */
        $paramsSetUserCounters['countPrivateBlueprint'] = Application::getDatabase()->count(
            $sqlPrivateBlueprints,
            ['userID' => $userID]
        );

        /* @noinspection NullPointerExceptionInspection */
        $paramsSetUserCounters['countPublicComment'] = Application::getDatabase()->count(
            $sqlPublicComments,
            ['userID' => $userID]
        );

        /* @noinspection NullPointerExceptionInspection */
        $paramsSetUserCounters['countPrivateComment'] = Application::getDatabase()->count(
            $sqlPrivateComments,
            ['userID' => $userID]
        );

        /* @noinspection NullPointerExceptionInspection */
        Application::getDatabase()->update($sqlSetUserCounters, $paramsSetUserCounters);
    }

    /**
     * @throws ApplicationException
     * @throws DatabaseException
     * @throws EnvironmentException
     */
    protected function setSoftDeleteAnonymousPrivateBlueprints(): void
    {
        $anonymousID = (int) Application::getConfig()->get('ANONYMOUS_ID');
        if ($anonymousID === 0) {
            return;
        }

        $forceRollback = false;
        $sqlSetSoftDeletedAnonymousPrivateBlueprints = <<<'SQL'
            UPDATE blueprints
            SET deleted_at = utc_timestamp()
            WHERE id_author = :userID
              AND exposure = 'private'
        SQL;

        try {
            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->startTransaction();

            /* @noinspection NullPointerExceptionInspection */
            Application::getDatabase()->update($sqlSetSoftDeletedAnonymousPrivateBlueprints, ['userID' => $anonymousID]);
        } catch (\Exception $e) {
            $forceRollback = true;
        } finally {
            if ($forceRollback) {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->rollbackTransaction();
            } else {
                /* @noinspection NullPointerExceptionInspection */
                Application::getDatabase()->completeTransaction();
            }
        }
    }
}
