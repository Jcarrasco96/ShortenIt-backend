<?php

namespace ShortenIt\models;

use Ramsey\Uuid\Uuid;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Model;

class LinkModel extends Model
{

    protected static string $tableName = 'links';

    public string $id;
    public string $original_url;
    public string $short_code;
    public int $access_count;
    public string $created_at;

    /**
     * @throws NotFoundHttpException
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
            throw new NotFoundHttpException("Link with id $uuid not found");
        }

        return self::fromArray(array_shift($data));
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function create(array $data): false|self
    {
        $uuid = Uuid::uuid4()->toString();

        $shortCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

        $insertData = [
            'id' => $uuid,
            'original_url' => $data['url'],
            'short_code' => $shortCode,
        ];

        $inserted = (new InsertSafeQuery())
            ->from(self::$tableName)
            ->data($insertData)
            ->execute();

        if ($inserted) {
            return self::findById($uuid);
        }

        return false;
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function update(string $uuid, array $data): false|self
    {
        unset($data['id']);

        $updated = (new UpdateSafeQuery())
            ->from(self::$tableName)
            ->data($data)
            ->where('id', $uuid)
            ->execute();

        if ($updated) {
            return self::findById($uuid);
        }

        return false;
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->applyQueryParams($_GET)
            ->execute();

        return array_map(static fn(array $data) => self::fromArray($data), $data);
    }

    protected static function fromArray(array $data): self
    {
        $props = ['id', 'original_url', 'short_code', 'access_count', 'created_at'];
        $obj = new self();
        foreach ($props as $prop) {
            if (isset($data[$prop])) {
                $obj->$prop = $data[$prop];
            }
        }
        return $obj;
    }

    public function __get(string $name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return null;
    }

    public static function exists(string $field, string $url): bool
    {
        return (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where($field, $url)
            ->limit(1)
            ->exists();
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findByCode(string $code): self
    {
        $data = (new SelectSafeQuery())
            ->from(self::$tableName)
            ->data()
            ->where('short_code', $code)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            throw new NotFoundHttpException("Link with id $code not found");
        }

        return self::fromArray(array_shift($data));
    }

}