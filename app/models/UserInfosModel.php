<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\Model;

class UserInfosModel extends Model
{
    /** @throws \Rancoud\Model\FieldException */
    protected function setFields(): void
    {
        $this->fields = [
            'id_user'                 => new Field('int', ['not_null', 'unsigned', 'pk']),
            'count_public_blueprint'  => new Field('int', ['not_null'], 0),
            'count_public_comment'    => new Field('int', ['not_null'], 0),
            'count_private_blueprint' => new Field('int', ['not_null'], 0),
            'count_private_comment'   => new Field('int', ['not_null'], 0),
            'bio'                     => new Field('text'),
            'link_website'            => new Field('varchar', ['max:255']),
            'link_facebook'           => new Field('varchar', ['max:255']),
            'link_twitter'            => new Field('varchar', ['max:255']),
            'link_github'             => new Field('varchar', ['max:255']),
            'link_twitch'             => new Field('varchar', ['max:255']),
            'link_unreal'             => new Field('varchar', ['max:255']),
            'link_youtube'            => new Field('varchar', ['max:255']),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'users_infos';
    }

    public function getDefaultUsersInfos(int $userID): array
    {
        return [
            'id_user'                 => $userID,
            'count_public_blueprint'  => 0,
            'count_public_comment'    => 0,
            'count_private_blueprint' => 0,
            'count_private_comment'   => 0,
            'avatar'                  => null,
            'bio'                     => null,
            'link_website'            => null,
            'link_facebook'           => null,
            'link_twitter'            => null,
            'link_github'             => null,
            'link_twitch'             => null,
            'link_unreal'             => null,
            'link_youtube'            => null,
        ];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePublicAndPrivateBlueprintCount(int $userID, int $count): void
    {
        $countPublicBlueprint = $count;
        $countPrivateBlueprint = $count;

        if ($count < 0) {
            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                return;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_public_blueprint'] + $count) < 0) {
                $countPublicBlueprint = 0;
            }

            if (($values['count_private_blueprint'] + $count) < 0) {
                $countPrivateBlueprint = 0;
            }
        }

        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_public_blueprint = count_public_blueprint + :countPublicBlueprint,
                count_private_blueprint = count_private_blueprint + :countPrivateBlueprint
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, [
            'userID'                => $userID,
            'countPublicBlueprint'  => $countPublicBlueprint,
            'countPrivateBlueprint' => $countPrivateBlueprint
        ]);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePrivateBlueprintCount(int $userID, int $count): void
    {
        $countPrivateBlueprint = $count;

        if ($count < 0) {
            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                return;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_private_blueprint'] + $count) < 0) {
                $countPrivateBlueprint = 0;
            }
        }

        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_private_blueprint = count_private_blueprint + :countPrivateBlueprint
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'countPrivateBlueprint' => $countPrivateBlueprint]);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePublicAndPrivateCommentCount(int $userID, int $count): void
    {
        $countPublicComment = $count;
        $countPrivateComment = $count;

        if ($count < 0) {
            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                return;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_public_comment'] + $count) < 0) {
                $countPublicComment = 0;
            }

            if (($values['count_private_comment'] + $count) < 0) {
                $countPrivateComment = 0;
            }
        }

        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_public_comment = count_public_comment + :countPublicComment,
                count_private_comment = count_private_comment + :countPrivateComment
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, [
            'userID'              => $userID,
            'countPublicComment'  => $countPublicComment,
            'countPrivateComment' => $countPrivateComment
        ]);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePrivateCommentCount(int $userID, int $count): void
    {
        $countPrivateComment = $count;

        if ($count < 0) {
            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                return;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_private_comment'] + $count) < 0) {
                $countPrivateComment = 0;
            }
        }

        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_private_comment = count_private_comment + :countPrivateComment
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'countPrivateComment' => $countPrivateComment]);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePublicAndPrivateCommentCountWithComments(array $comments): void
    {
        // always +n
        $users = [];
        foreach ($comments as $comment) {
            if (!isset($users[$comment['id_author']])) {
                $users[$comment['id_author']] = 0;
            }
            ++$users[$comment['id_author']];
        }

        // @codeCoverageIgnoreStart
        /*
         * not possible because previous checks has been done
         */
        if (empty($users)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($users as $userID => $count) {
            $countPublicComment = $count;
            $countPrivateComment = $count;

            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                continue;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_public_comment'] - $count) < 0) {
                $countPublicComment = 0;
            }

            if (($values['count_private_comment'] - $count) < 0) {
                $countPrivateComment = 0;
            }

            $sql = <<<'SQL'
                UPDATE users_infos
                SET count_public_comment = count_public_comment - :countPublicComment,
                    count_private_comment = count_private_comment - :countPrivateComment
                WHERE id_user = :userID;
            SQL;

            $this->database->update($sql, [
                'userID'              => $userID,
                'countPublicComment'  => $countPublicComment,
                'countPrivateComment' => $countPrivateComment,
            ]);
        }
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function updatePrivateCommentCountWithComments(array $comments): void
    {
        $users = [];
        foreach ($comments as $comment) {
            if (!isset($users[$comment['id_author']])) {
                $users[$comment['id_author']] = 0;
            }
            ++$users[$comment['id_author']];
        }

        // @codeCoverageIgnoreStart
        /*
         * not possible because previous checks has been done
         */
        if (empty($users)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($users as $userID => $count) {
            $countPrivateComment = $count;

            $values = $this->getBlueprintsAndCommentsCount($userID);
            if (empty($values)) {
                // @codeCoverageIgnoreStart
                /*
                 * It is not possible to reach this statement because user is checked before.
                 */
                continue;
                // @codeCoverageIgnoreEnd
            }

            if (($values['count_private_comment'] - $count) < 0) {
                $countPrivateComment = 0;
            }

            $sql = <<<'SQL'
                UPDATE users_infos
                SET count_private_comment = count_private_comment - :countPrivateComment
                WHERE id_user = :userID;
            SQL;

            $this->database->update($sql, ['userID' => $userID, 'countPrivateComment' => $countPrivateComment]);
        }
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    protected function getBlueprintsAndCommentsCount(int $userID): ?array
    {
        $sql = <<<'SQL'
                SELECT count_public_blueprint, count_private_blueprint, count_public_comment, count_private_comment
                FROM users_infos
                WHERE id_user = :userID;
            SQL;

        $row = $this->database->selectRow($sql, ['userID' => $userID]);
        if (empty($row)) {
            // @codeCoverageIgnoreStart
            /*
             * It is not possible to reach this statement because user is checked before.
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->formatValues($row);
    }
}
