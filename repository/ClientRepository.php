<?php

namespace ShortenIt\repository;

use Ramsey\Uuid\Uuid;
use ShortenIt\models\Client;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\DeleteSafeQuery;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Repository;

class ClientRepository extends Repository
{

    protected static function tableName(): string
    {
        return 'client';
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->applyQueryParams($_GET)
            ->execute();

        return array_map(static fn(array $data) => Client::fromArray($data), $data);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findById(string $uuid, bool $throwsOnError = false): Client
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

        return Client::fromArray(array_shift($data));
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function create(array $data): Client
    {
        $uuid = Uuid::uuid4()->toString();

        $insertData = [
            'id' => $uuid,
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
    public static function update(string $uuid, array $data): Client
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

}