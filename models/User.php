<?php

namespace ShortenIt\models;

use Exception;
use Ramsey\Uuid\Uuid;
use Random\RandomException;
use SimpleApiRest\core\Security;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\models\UserIdentity;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;

/**
 *
 * @property array[] $permissions
 */
class User extends UserIdentity
{

    protected static string $tableName = 'user';

    public string $id;
    public string $username;

    /**
     * @throws Exception
     */
    public static function findById(string $uuid): self
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where('id', $uuid)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            throw new NotFoundHttpException('User not found');
        }

        return self::fromArray(array_shift($data));
    }

    /**
     * @throws RandomException
     * @throws BadRequestHttpException
     */
    public static function create(array $data): false|self
    {
        $exist = self::exist($data['username']);

        if ($exist) {
            throw new BadRequestHttpException('Username already exists.');
        }

        $uuid = Uuid::uuid4()->toString();

        $created = (bool)(new InsertSafeQuery())
            ->from(self::$tableName)
            ->data([
                'id' => $uuid,
                'username' => $data['username'],
                'password' => Security::generatePasswordHash($data['password']),
            ])
            ->execute();

        if ($created) {
            $data = (new SelectSafeQuery())
                ->from(self::$tableName)
                ->data()
                ->where('id', $uuid)
                ->limit(1)
                ->execute();

            if (empty($data)) {
                return false;
            }

            return self::fromArray(array_shift($data));
        }

        return false;
    }

    public static function update(string $uuid, array $data): false|self
    {
        // TODO: Implement update() method.
        return false;
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->execute();

        return array_map(static fn(array $data) => self::fromArray($data, false), $data);
    }

    protected static function fromArray(array $data): self
    {
        $obj = new self();
        $obj->id = $data['id'];
        $obj->username = $data['username'];
        return $obj;
    }

    public static function findOne(string $id): ?self
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where('id', $id)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            return null;
        }

        return self::fromArray(array_shift($data));
    }

    /**
     * @throws BadRequestHttpException
     */
    public static function findByCredentials(string $username, string $password): ?User
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where('username', $username)
            ->execute();

        $data = array_shift($data);

        if ($data && Security::validatePassword($password, $data['password'])) {
            return self::fromArray($data);
        }

        return null;
    }

    public static function exist(string $username): bool
    {
        return (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where('username', $username)
            ->exists();
    }

    public function __get(string $name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        if ($name === 'permissions') {
            $data = (new SelectSafeQuery())
                ->from('authentication')
                ->data(['item_name'])
                ->where('user_id', $this->id)
                ->execute();

            $this->attributes[$name] = array_column($data, 'item_name');

            return $this->attributes[$name];
        }

        return null;
    }

}
