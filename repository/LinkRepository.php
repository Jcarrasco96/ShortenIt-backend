<?php

namespace ShortenIt\repository;

use Ramsey\Uuid\Uuid;
use ShortenIt\models\Link;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\DeleteSafeQuery;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Repository;

class LinkRepository extends Repository
{

    protected static function tableName(): string
    {
        return 'links';
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->applyQueryParams($_GET)
            ->execute();

        return array_map(static fn(array $data) => Link::fromArray($data), $data);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findById(string $uuid, bool $throwsOnError = false): Link
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('id', $uuid)
            ->limit(1)
            ->execute();

        if (empty($data) && $throwsOnError) {
            throw new NotFoundHttpException("Link with id $uuid not found");
        }

        return Link::fromArray(array_shift($data));
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function create(array $data): Link
    {
        $uuid = Uuid::uuid4()->toString();

        $shortCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);

        $insertData = [
            'id' => $uuid,
            'original_url' => $data['url'],
            'short_code' => $shortCode,
        ];

        (new InsertSafeQuery())
            ->from(self::tableName())
            ->data($insertData)
            ->execute();

        return self::findById($uuid);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function update(string $uuid, array $data): Link
    {
        unset($data['id']);

        (new UpdateSafeQuery())
            ->from(self::tableName())
            ->data($data)
            ->where('id', $uuid)
            ->execute();

        return self::findById($uuid);
    }

    public static function delete(string $uuid): bool
    {
        return (new DeleteSafeQuery())
            ->from(self::tableName())
            ->where('id', $uuid)
            ->execute();
    }

    public static function exists(string $field, string $url): bool
    {
        return (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where($field, $url)
            ->limit(1)
            ->exists();
    }

    public static function findByCode(string $code): ?Link
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('short_code', $code)
            ->limit(1)
            ->execute();

        return empty($data) ? null : Link::fromArray(array_shift($data));

    }



}