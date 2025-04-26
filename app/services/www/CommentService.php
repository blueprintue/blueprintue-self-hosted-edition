<?php

declare(strict_types=1);

namespace app\services\www;

use app\helpers\Helper;
use app\models\CommentModel;
use Rancoud\Application\Application;

class CommentService
{
    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getAllCommentsWithBlueprintID(int $blueprintID): ?array
    {
        return (new CommentModel(Application::getDatabase()))->getAllCommentsWithBlueprintID($blueprintID);
    }

    // region Add comment
    /**
     * @throws \Exception
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function addComment(int $blueprintID, int $userID, string $comment): int
    {
        return (new CommentModel(Application::getDatabase()))
            ->create(['id_blueprint' => $blueprintID, 'id_author' => $userID, 'content' => $comment, 'created_at' => Helper::getNowUTCFormatted()]);
    }
    // endregion

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function editComment(int $commentID, string $comment): void
    {
        (new CommentModel(Application::getDatabase()))
            ->update(['content' => $comment], $commentID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function deleteComment(int $commentID): void
    {
        (new CommentModel(Application::getDatabase()))
            ->delete($commentID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function deleteCommentsWithBlueprintID(int $blueprintID): void
    {
        (new CommentModel(Application::getDatabase()))
            ->deleteCommentsWithBlueprintID($blueprintID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function keepComments(int $userID, string $nameFallback): void
    {
        (new CommentModel(Application::getDatabase()))
            ->changeAuthor($userID, null, $nameFallback);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function anonymizeComments(int $userID): void
    {
        (new CommentModel(Application::getDatabase()))
            ->changeAuthor($userID, null, 'Guest');
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function deleteFromAuthor(int $userID): void
    {
        (new CommentModel(Application::getDatabase()))
            ->deleteFromAuthor($userID);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     */
    public static function isCommentBelongToAuthor(int $commentID, int $userID): bool
    {
        return (new CommentModel(Application::getDatabase()))
            ->isCommentBelongToAuthor($commentID, $userID);
    }
}
