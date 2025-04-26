<?php

declare(strict_types=1);

namespace app\models;

use Rancoud\Crypt\Crypt;
use Rancoud\Database\Database;
use Rancoud\Model\Field;
use Rancoud\Model\FieldException;
use Rancoud\Model\Model;

class UserModel extends Model
{
    public function __construct(Database $database)
    {
        $this->addInternalsCallbacks();

        parent::__construct($database);
    }

    /** @throws FieldException */
    protected function setFields(): void
    {
        $this->fields = [
            'id'                => new Field('int', ['not_null', 'unsigned', 'pk']),
            'username'          => new Field('varchar', ['max:100', 'not_null']),
            'password'          => new Field('varchar'),
            'slug'              => new Field('varchar', ['max:100', 'not_null']),
            'email'             => new Field('varchar', ['max:100', 'email']),
            'password_reset'    => new Field('varchar', ['max:255']),
            'password_reset_at' => new Field('datetime'),
            'grade'             => new Field('enum:member,admin', ['not_null'], 'member'),
            'avatar'            => new Field('varchar', ['max:255']),
            'remember_token'    => new Field('char', ['min:255', 'max:255']),
            'created_at'        => new Field('datetime', ['not_null']),
            'confirmed_token'   => new Field('char', ['min:255', 'max:255']),
            'confirmed_sent_at' => new Field('datetime'),
            'confirmed_at'      => new Field('datetime'),
            'last_login_at'     => new Field('datetime')
        ];
    }

    protected function setTable(): void
    {
        $this->table = 'users';
    }

    protected function addInternalsCallbacks(): void
    {
        $this->addInternalCallbackPassword();
    }

    protected function addInternalCallbackPassword(): void
    {
        $this->addBeforeCreate(
            'cryptPassword',
            static function (string $sql, array $params): array {
                if ($params['password'] !== null) {
                    $params['password'] = Crypt::hash($params['password']);
                }

                return [$sql, $params];
            }
        );

        $this->addBeforeUpdate(
            'cryptPassword',
            static function (string $sql, array $params): ?array {
                if (!\array_key_exists('password', $params)) {
                    return null;
                }

                if ($params['password'] !== null) {
                    $params['password'] = Crypt::hash($params['password']);
                }

                return [$sql, $params];
            }
        );
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function findUserIDWithUsernameAndPassword(string $login, string $password): ?int
    {
        $sql = <<<'SQL'
            SELECT id, password
            FROM users
            WHERE username = :username
              AND password IS NOT NULL;
        SQL;
        $params = ['username' => $login];

        $rows = $this->database->selectAll($sql, $params);

        if (empty($rows)) {
            return null;
        }

        foreach ($rows as $row) {
            if (Crypt::verify($password, $row['password'])) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getInfosForSession(int $userID): ?array
    {
        $sql = <<<'SQL'
            SELECT username, slug, grade
            FROM users
            WHERE id = :id;
        SQL;
        $params = ['id' => $userID];
        $row = $this->database->selectRow($sql, $params);

        if (empty($row)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because checks has been done before
             * For covering we have to test function only
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        return $this->formatValues($row);
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getInfosFromIdAuthorIndex(array $items): array
    {
        $tmpIDs = [];
        foreach ($items as $item) {
            if (isset($item['id_author'])) {
                $tmpID = (int) $item['id_author'];
                if ($tmpID > 0) {
                    $tmpIDs[] = $tmpID;
                }
            }
        }

        if (empty($tmpIDs)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because checks has been done before
             * For covering we have to test function only
             */
            return [];
            // @codeCoverageIgnoreEnd
        }

        $inStr = \implode(',', \array_unique($tmpIDs));
        $sql = <<<SQL
            SELECT id, username, slug, avatar
            FROM users
            WHERE id IN ({$inStr});
        SQL;

        $rows = $this->database->selectAll($sql);

        $newRowsIndexedByID = [];
        foreach ($rows as $row) {
            $newRowsIndexedByID[$row['id']] = $this->formatValues($row);
        }

        return $newRowsIndexedByID;
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function isUsernameAvailable(string $username, string $slug): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(*)
            FROM users
            WHERE username = :username OR slug = :slug;
        SQL;
        $params = ['username' => $username, 'slug' => $slug];

        return $this->database->count($sql, $params) === 0;
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function isEmailAvailable(string $email): bool
    {
        $sql = <<<'SQL'
            SELECT COUNT(*)
            FROM users
            WHERE email = :email;
        SQL;
        $params = ['email' => $email];

        return $this->database->count($sql, $params) === 0;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function getUserBySlugForPublicProfile(string $slug): ?array
    {
        $sql = <<<'SQL'
            SELECT id, username, slug, grade, avatar
            FROM users
            WHERE slug = :slug;
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
    public function getUserByIDForPrivateProfile(int $userID): ?array
    {
        $sql = <<<'SQL'
            SELECT id, username, slug, email, grade, avatar, IF(password IS NOT NULL, true, false) AS has_password
            FROM users
            WHERE id = :id;
        SQL;
        $params = ['id' => $userID];

        $row = $this->database->selectRow($sql, $params);
        if (empty($row)) {
            // @codeCoverageIgnoreStart
            /*
             * In end 2 end testing we can't arrive here because checks has been done before
             * For covering we have to test function only
             */
            return null;
            // @codeCoverageIgnoreEnd
        }

        $cleanRow = $this->formatValues($row);

        $cleanRow['has_password'] = (bool) $row['has_password'];

        return $cleanRow;
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function deleteRememberToken(int $userID): void
    {
        $sql = <<<'SQL'
            UPDATE users
            SET remember_token = NULL
            WHERE id = :id;
        SQL;
        $params = ['id' => $userID];

        $this->database->update($sql, $params);
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function getUserIDFromRememberMe(string $rememberToken): ?int
    {
        $sql = <<<'SQL'
            SELECT id
            FROM users
            WHERE remember_token = :remember_token;
        SQL;
        $params = ['remember_token' => $rememberToken];

        $userID = (int) $this->database->selectVar($sql, $params);

        if ($userID > 0) {
            return $userID;
        }

        return null;
    }

    /**
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public function findUserWithEmailForResetPassword(string $email): ?array
    {
        $sql = <<<'SQL'
            SELECT id, username, password_reset, password_reset_at
            FROM users
            WHERE email = :email;
        SQL;
        $params = ['email' => $email];

        $row = $this->database->selectRow($sql, $params);
        if (empty($row)) {
            return null;
        }

        return $this->formatValues($row);
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function findUserIDFromEmailAndToken(string $email, string $token): ?int
    {
        $sql = <<<'SQL'
            SELECT id
            FROM users
            WHERE email = :email
              AND password_reset = :token;
        SQL;
        $params = ['email' => $email, 'token' => $token];

        $userID = (int) $this->database->selectVar($sql, $params);

        if ($userID > 0) {
            return $userID;
        }

        return null;
    }

    /** @throws \Rancoud\Database\DatabaseException */
    public function findUserIDWithConfirmedToken(string $confirmedToken): ?int
    {
        $sql = <<<'SQL'
            SELECT id
            FROM users
            WHERE confirmed_token = :confirmed_token
              AND confirmed_at IS NULL;
        SQL;
        $params = ['confirmed_token' => $confirmedToken];

        $userID = (int) $this->database->selectVar($sql, $params);

        if ($userID > 0) {
            return $userID;
        }

        return null;
    }
}
