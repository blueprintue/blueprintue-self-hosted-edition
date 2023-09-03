<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\Model;

class UserInfosModel extends Model
{
    /**
     * @throws \Rancoud\Model\FieldException
     */
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

    /**
     * @param int $userID
     *
     * @return array
     */
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
     * @param int $userID
     * @param int $count
     *
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updatePublicAndPrivateBlueprintCount(int $userID, int $count): void
    {
        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_public_blueprint = count_public_blueprint + :number,
                count_private_blueprint = count_private_blueprint + :number
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'number' => $count]);
    }

    /**
     * @param int $userID
     * @param int $count
     *
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updatePrivateBlueprintCount(int $userID, int $count): void
    {
        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_private_blueprint = count_private_blueprint + :number
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'number' => $count]);
    }

    /**
     * @param int $userID
     * @param int $count
     *
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updatePublicAndPrivateCommentCount(int $userID, int $count): void
    {
        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_public_comment = count_public_comment + :number,
                count_private_comment = count_private_comment + :number
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'number' => $count]);
    }

    /**
     * @param int $userID
     * @param int $count
     *
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updatePrivateCommentCount(int $userID, int $count): void
    {
        $sql = <<<'SQL'
            UPDATE users_infos
            SET count_private_comment = count_private_comment + :number
            WHERE id_user = :userID;
        SQL;

        $this->database->update($sql, ['userID' => $userID, 'number' => $count]);
    }

    /**
     * @param array $comments
     *
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updatePublicAndPrivateCommentCountWithComments(array $comments): void
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
            $sql = <<<'SQL'
                UPDATE users_infos
                SET count_public_comment = count_public_comment - :count,
                    count_private_comment = count_private_comment - :count
                WHERE id_user = :userID;
            SQL;
            $this->database->update($sql, ['userID' => $userID, 'count' => $count]);
        }
    }

    /**
     * @param array $comments
     *
     * @throws \Rancoud\Database\DatabaseException
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
            $sql = <<<'SQL'
                UPDATE users_infos
                SET count_private_comment = count_private_comment - :count
                WHERE id_user = :userID;
            SQL;
            $this->database->update($sql, ['userID' => $userID, 'count' => $count]);
        }
    }
}
