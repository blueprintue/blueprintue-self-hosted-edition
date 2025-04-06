<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\FieldException;
use Rancoud\Model\Model;

class BlueprintModel extends Model
{
    /**
     * @throws FieldException
     */
    protected function setFields(): void
    {
        $this->fields = [
            'id'              => new Field('int', ['not_null', 'unsigned', 'pk']),
            'id_author'       => new Field('int', ['unsigned', 'fk']),
            'slug'            => new Field('varchar', ['max:100', 'not_null']),
            'file_id'         => new Field('varchar', ['max:100', 'not_null']),
            'title'           => new Field('varchar', ['max:255', 'not_null']),
            'type'            => new Field('enum:animation,behavior_tree,blueprint,material,metasound,niagara,pcg', ['not_null'], 'blueprint'), // phpcs:ignore
            'ue_version'      => new Field('varchar', ['max:5', 'not_null'], '4.25'),
            'current_version' => new Field('int', ['unsigned', 'not_null']),
            'thumbnail'       => new Field('varchar', ['max:255']),
            'description'     => new Field('text'),
            'exposure'        => new Field('enum:public,unlisted,private', ['not_null'], 'public'),
            'expiration'      => new Field('datetime'),
            'tags'            => new Field('varchar', ['max:255']),
            'video'           => new Field('varchar', ['max:255']),
            'video_provider'  => new Field('varchar', ['max:255']),
            'comments_hidden' => new Field('int', ['range:0,1', 'not_null'], 0),
            'comments_closed' => new Field('int', ['range:0,1', 'not_null'], 0),
            'comments_count'  => new Field('int', ['unsigned'], 0),
            'created_at'      => new Field('datetime', ['not_null']),
            'updated_at'      => new Field('datetime'),
            'published_at'    => new Field('datetime'),
            'deleted_at'      => new Field('datetime'),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'blueprints';
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getLastFive(): ?array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM blueprints
            WHERE deleted_at IS NULL
              AND id_author IS NOT NULL
              AND published_at IS NOT NULL
              AND exposure = 'public'
              AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
            ORDER BY published_at DESC
            LIMIT 5 OFFSET 0
        SQL;

        $rows = $this->database->selectAll($sql);
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
    public function isNewFileIDAvailable(string $fileID): ?bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(slug)
            FROM blueprints
            WHERE file_id = :fileID
        SQL;
        $params = ['fileID' => $fileID];

        $count = $this->database->count($sql, $params);

        return $count === 0;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getFromSlug(string $slug): ?array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM blueprints
            WHERE slug = :slug
                AND deleted_at IS NULL
                AND id_author IS NOT NULL
                AND published_at IS NOT NULL
                AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
        SQL;
        $params = ['slug' => $slug];

        $row = $this->database->selectRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return $this->formatValues($row);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function changeAuthor(int $fromID, int $toID): void
    {
        $sql = <<<'SQL'
            UPDATE blueprints
            SET id_author = :toID
            WHERE id_author = :fromID;
        SQL;
        $params = ['toID' => $toID, 'fromID' => $fromID];

        $this->database->update($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function softDeleteFromAuthor(int $id): void
    {
        $sql = <<<'SQL'
            UPDATE blueprints
            SET deleted_at = utc_timestamp(),
                id_author = null
            WHERE id_author = :id;
        SQL;
        $params = ['id' => $id];

        $this->database->update($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function isAuthorBlueprint(int $blueprintID, int $userID): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(*)
            FROM blueprints
            WHERE id = :blueprintID
              AND id_author = :userID;
        SQL;
        $params = ['blueprintID' => $blueprintID, 'userID' => $userID];

        return $this->database->count($sql, $params) === 1;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function searchWithAuthor(int $userID, bool $showOnlyPublic, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        if ($showOnlyPublic) {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE id_author = :userID
                  AND deleted_at IS NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE id_author = :userID
                  AND deleted_at IS NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;
        } else {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE id_author = :userID
                  AND deleted_at IS NULL
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE id_author = :userID
                  AND deleted_at IS NULL
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;
        }
        $paramsRows = ['userID' => $userID, 'count' => $count, 'offset' => $offset];
        $paramsCountRows = ['userID' => $userID];

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function searchLast(?int $connectedUserID, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        if ($connectedUserID !== null) {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['userID' => $connectedUserID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['userID' => $connectedUserID];
        } else {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['count' => $count, 'offset' => $offset];
            $paramsCountRows = [];
        }

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function searchMostDiscussed(?int $connectedUserID, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        if ($connectedUserID !== null) {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND comments_hidden = 0
                  AND comments_count > 0
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY comments_count DESC, published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND comments_hidden = 0
                  AND comments_count > 0
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['userID' => $connectedUserID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['userID' => $connectedUserID];
        } else {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND comments_hidden = 0
                  AND comments_count > 0
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY comments_count DESC, published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND comments_hidden = 0
                  AND comments_count > 0
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['count' => $count, 'offset' => $offset];
            $paramsCountRows = [];
        }

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function searchType(string $type, ?int $connectedUserID, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        if ($connectedUserID !== null) {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND type = :type
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND type = :type
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['type' => $type, 'userID' => $connectedUserID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['type' => $type, 'userID' => $connectedUserID];
        } else {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND type = :type
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND type = :type
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['type' => $type, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['type' => $type];
        }

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function searchTag(int $tagID, ?int $connectedUserID, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        if ($connectedUserID !== null) {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND FIND_IN_SET(:tagID, tags)
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND FIND_IN_SET(:tagID, tags)
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['tagID' => $tagID, 'userID' => $connectedUserID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['tagID' => $tagID, 'userID' => $connectedUserID];
        } else {
            $sqlRows = <<<'SQL'
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND FIND_IN_SET(:tagID, tags)
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<'SQL'
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND FIND_IN_SET(:tagID, tags)
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP());
            SQL;

            $paramsRows = ['tagID' => $tagID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['tagID' => $tagID];
        }

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @param array $params [query,type,ue_version]
     *
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function search(array $params, ?int $connectedUserID, array $pagination): ?array
    {
        [$count, $offset] = \Rancoud\Model\Helper::getLimitOffsetCount($pagination);

        $queryValue = '';
        $querySQL = '';
        if ($params['query'] !== '') {
            $queryValue = '%' . \str_replace(['%', '_'], ['\%', '\_'], $params['query']) . '%';
            $querySQL = 'AND (title LIKE :query OR description LIKE :query)';
        }

        $typeSQL = '';
        if ($params['type'] !== '') {
            $typeSQL = 'AND type = :type';
        }

        $ueVersionSQL = '';
        if ($params['ue_version'] !== '') {
            $ueVersionSQL = 'AND ue_version = :ue_version';
        }

        if ($connectedUserID !== null) {
            // just for IDE
            $limit = 'LIMIT :offset, :count';
            $sqlRows = <<<SQL
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                  $querySQL
                  $typeSQL
                  $ueVersionSQL
                ORDER BY published_at DESC
                $limit;
            SQL;
            $sqlCountRows = <<<SQL
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND (exposure = 'public' OR id_author = :userID)
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                  $querySQL
                  $typeSQL
                  $ueVersionSQL;
            SQL;

            $paramsRows = ['userID' => $connectedUserID, 'count' => $count, 'offset' => $offset];
            $paramsCountRows = ['userID' => $connectedUserID];

            if ($querySQL !== '') {
                $paramsRows['query'] = $queryValue;
                $paramsCountRows['query'] = $queryValue;
            }

            if ($typeSQL !== '') {
                $paramsRows['type'] = $params['type'];
                $paramsCountRows['type'] = $params['type'];
            }

            if ($ueVersionSQL !== '') {
                $paramsRows['ue_version'] = $params['ue_version'];
                $paramsCountRows['ue_version'] = $params['ue_version'];
            }
        } else {
            $sqlRows = <<<SQL
                SELECT *
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                  $querySQL
                  $typeSQL
                  $ueVersionSQL
                ORDER BY published_at DESC
                LIMIT :offset, :count;
            SQL;
            $sqlCountRows = <<<SQL
                SELECT COUNT(*)
                FROM blueprints
                WHERE deleted_at IS NULL
                  AND id_author IS NOT NULL
                  AND published_at IS NOT NULL
                  AND exposure = 'public'
                  AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
                  $querySQL
                  $typeSQL
                  $ueVersionSQL;
            SQL;

            $paramsRows = ['count' => $count, 'offset' => $offset];
            $paramsCountRows = [];

            if ($querySQL !== '') {
                $paramsRows['query'] = $queryValue;
                $paramsCountRows['query'] = $queryValue;
            }

            if ($typeSQL !== '') {
                $paramsRows['type'] = $params['type'];
                $paramsCountRows['type'] = $params['type'];
            }

            if ($ueVersionSQL !== '') {
                $paramsRows['ue_version'] = $params['ue_version'];
                $paramsCountRows['ue_version'] = $params['ue_version'];
            }
        }

        $rows = $this->database->selectAll($sqlRows, $paramsRows);
        if (empty($rows)) {
            return ['rows' => null, 'count' => 0];
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return ['rows' => $rows, 'count' => $this->database->count($sqlCountRows, $paramsCountRows)];
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function updateCommentCount(int $blueprintID, int $count): void
    {
        $sql = <<<'SQL'
            UPDATE blueprints
            SET comments_count = comments_count + :count
            WHERE id = :blueprintID;
        SQL;
        $params = ['count' => $count, 'blueprintID' => $blueprintID];

        $this->database->update($sql, $params);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function getTagsFromPublicBlueprints(?int $connectedUserID): array
    {
        if ($connectedUserID !== null) {
            $sql = <<<'SQL'
            SELECT tags
            FROM blueprints
            WHERE deleted_at IS NULL
              AND id_author IS NOT NULL
              AND published_at IS NOT NULL
              AND (exposure = 'public' OR id_author = :userID)
              AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
              AND tags <> ''
              AND tags IS NOT NULL
        SQL;
            $params = ['userID' => $connectedUserID];
        } else {
            $sql = <<<'SQL'
            SELECT tags
            FROM blueprints
            WHERE deleted_at IS NULL
              AND id_author IS NOT NULL
              AND published_at IS NOT NULL
              AND exposure = 'public'
              AND (expiration IS NULL OR expiration > UTC_TIMESTAMP())
              AND tags <> ''
              AND tags IS NOT NULL
        SQL;
            $params = [];
        }

        return $this->database->selectCol($sql, $params);
    }
}
