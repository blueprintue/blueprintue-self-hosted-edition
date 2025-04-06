<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\FieldException;
use Rancoud\Model\Model;

class CommentModel extends Model
{
    /**
     * @throws FieldException
     */
    protected function setFields(): void
    {
        $this->fields = [
            'id'            => new Field('int', ['not_null', 'unsigned', 'pk']),
            'id_author'     => new Field('int', ['unsigned', 'fk']),
            'id_blueprint'  => new Field('int', ['not_null', 'unsigned', 'fk']),
            'name_fallback' => new Field('varchar', ['max:255']),
            'content'       => new Field('text', ['not_null']),
            'created_at'    => new Field('datetime', ['not_null']),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'comments';
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     *
     * @return mixed
     */
    public function getAllCommentsWithBlueprintID(int $blueprintID): ?array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM comments
            WHERE id_blueprint = :blueprintID;
        SQL;
        $params = ['blueprintID' => $blueprintID];

        $rows = $this->database->selectAll($sql, $params);

        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return $rows;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     *
     * @return mixed
     */
    public function deleteCommentsWithBlueprintID(int $blueprintID): void
    {
        $sql = <<<'SQL'
            DELETE FROM comments
            WHERE id_blueprint = :blueprintID;
        SQL;
        $params = ['blueprintID' => $blueprintID];

        $this->database->delete($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function changeAuthor(int $fromID, ?int $toID, ?string $nameFallback): void
    {
        $sql = <<<'SQL'
            UPDATE comments
            SET id_author = :toID,
                name_fallback = :nameFallback
            WHERE id_author = :fromID;
        SQL;
        $params = ['toID' => $toID, 'fromID' => $fromID, 'nameFallback' => $nameFallback];

        $this->database->update($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function deleteFromAuthor(int $id): void
    {
        $sql = <<<'SQL'
            DELETE FROM comments
            WHERE id_author = :id;
        SQL;
        $params = ['id' => $id];

        $this->database->delete($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function isCommentBelongToAuthor(int $commentID, int $userID): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(id)
            FROM comments
            WHERE id = :commentID AND id_author = :authorID;
        SQL;
        $params = ['commentID' => $commentID, 'authorID' => $userID];

        return $this->database->count($sql, $params) === 1;
    }
}
