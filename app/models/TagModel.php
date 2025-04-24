<?php

declare(strict_types=1);

namespace app\models;

use app\helpers\Helper;
use Rancoud\Model\Field;
use Rancoud\Model\FieldException;
use Rancoud\Model\Model;

class TagModel extends Model
{
    /** @throws FieldException */
    protected function setFields(): void
    {
        $this->fields = [
            'id'   => new Field('int', ['not_null', 'unsigned', 'pk']),
            'name' => new Field('varchar', ['max:100', 'not_null']),
            'slug' => new Field('varchar', ['max:100', 'not_null']),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'tags';
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getTagsWithListIDs(string $listIDs): ?array
    {
        $listIDs = Helper::trim($listIDs);
        if (empty($listIDs)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because checks has been done before
             * For covering we have to test function only
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        $tagsIDs = [];
        $ids = \explode(',', $listIDs);
        foreach ($ids as $id) {
            $tmpID = (int) $id;
            if ($tmpID > 0) {
                $tagsIDs[] = $tmpID;
            }
        }

        $inStr = \implode(',', \array_unique($tagsIDs));
        $sql = <<<SQL
            SELECT *
            FROM tags
            WHERE id IN ($inStr);
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
     * @throws \Rancoud\Model\ModelException
     *
     * @return null
     */
    public function findTagWithSlug(string $slug): ?array
    {
        $sql = <<<'SQL'
            SELECT *
            FROM tags
            WHERE slug = :slug
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
     * @throws \Rancoud\Model\ModelException
     */
    public function findTagsWithSlugs(array $tagsToSeek): ?array
    {
        $sqlParts = [];
        $params = [];
        $i = 0;
        foreach ($tagsToSeek as $tagToSeek) {
            $sqlParts[] = 'slug = :tag_' . $i;
            $params['tag_' . $i] = $tagToSeek;
            ++$i;
        }

        if (empty($sqlParts)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because checks has been done before
             * For covering we have to test function only
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        $sqlPart = \implode(' OR ', $sqlParts);
        // @codeCoverageIgnoreStart
        // Coverage is messing here
        $sql = <<<SQL
            SELECT *
            FROM tags
            WHERE $sqlPart
        SQL;
        // @codeCoverageIgnoreEnd

        $rows = $this->database->selectAll($sql, $params);
        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $k => $row) {
            $rows[$k] = $this->formatValues($row);
        }

        return $rows;
    }
}
