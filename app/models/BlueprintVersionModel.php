<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\FieldException;
use Rancoud\Model\Model;

class BlueprintVersionModel extends Model
{
    /**
     * @throws FieldException
     */
    protected function setFields(): void
    {
        $this->fields = [
            'id'           => new Field('int', ['not_null', 'unsigned', 'pk']),
            'id_blueprint' => new Field('int', ['not_null', 'unsigned', 'fk']),
            'version'      => new Field('int', ['unsigned', 'not_null']),
            'reason'       => new Field('text'),
            'created_at'   => new Field('datetime', ['not_null']),
            'updated_at'   => new Field('datetime'),
            'published_at' => new Field('datetime'),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'blueprints_version';
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     *
     * @return array|null
     */
    public function getNextVersion(int $blueprintID): ?int
    {
        $sql = <<<'SQL'
            SELECT MAX(version) + 1
            FROM blueprints_version
            WHERE id_blueprint = :blueprintID
        SQL;
        $params = ['blueprintID' => $blueprintID];

        $nextVersion = (int) $this->database->selectVar($sql, $params);
        if (empty($nextVersion)) {
            return null;
        }

        return $nextVersion;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     *
     * @return mixed
     */
    public function getAllVersions(int $blueprintID): ?array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM blueprints_version
            WHERE id_blueprint = :blueprintID
            ORDER BY created_at DESC, id DESC;
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
     */
    public function deleteWithBlueprintID(int $blueprintID): void
    {
        $sql = <<<'SQL'
            DELETE
            FROM blueprints_version
            WHERE id_blueprint = :blueprintID;
        SQL;
        $params = ['blueprintID' => $blueprintID];

        $this->database->delete($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function deleteWithBlueprintIDAndVersion(int $blueprintID, int $version): void
    {
        $sql = <<<'SQL'
            DELETE
            FROM blueprints_version
            WHERE id_blueprint = :blueprintID
                AND version = :version;
        SQL;
        $params = ['blueprintID' => $blueprintID, 'version' => $version];

        $this->database->delete($sql, $params);
    }
}
