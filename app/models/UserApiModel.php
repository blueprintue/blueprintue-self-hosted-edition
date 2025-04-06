<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Model\Field;
use Rancoud\Model\Model;

class UserApiModel extends Model
{
    /**
     * @throws \Rancoud\Model\FieldException
     */
    protected function setFields(): void
    {
        $this->fields = [
            'id_user' => new Field('int', ['not_null', 'unsigned', 'pk']),
            'api_key' => new Field('varchar', ['max:100', 'not_null']),
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'users_api';
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function getApiKey(int $userID): ?string
    {
        $sql = <<<'SQL'
                SELECT api_key
                FROM users_api
                WHERE id_user = :userID;
            SQL;
        $params = ['userID' => $userID];

        $apiKey = $this->database->selectVar($sql, $params);

        if (empty($apiKey)) {
            return null;
        }

        return (string) $apiKey;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function isApiKeyAvailable(string $apiKey): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(api_key)
            FROM users_api
            WHERE api_key = :apiKey;
        SQL;
        $params = ['apiKey' => $apiKey];

        $count = $this->database->count($sql, $params);

        return $count === 0;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     */
    public function getUserID(string $apiKey): ?int
    {
        $sql = <<<'SQL'
            SELECT id_user
            FROM users_api
            WHERE api_key = :apiKey;
        SQL;
        $params = ['apiKey' => $apiKey];

        $userID = (int) $this->database->selectVar($sql, $params);
        if ($userID === 0) {
            return null;
        }

        return $userID;
    }
}
