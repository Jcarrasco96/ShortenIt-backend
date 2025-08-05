<?php

namespace ShortenIt\repository;

use JetBrains\PhpStorm\ExpectedValues;
use Ramsey\Uuid\Uuid;
use ShortenIt\helpers\Constants;
use ShortenIt\models\Authentication;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\DeleteSafeQuery;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Repository;

class AuthenticationRepository extends Repository
{

    protected static function tableName(): string
    {
        return 'authentication';
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->applyQueryParams($_GET)
            ->execute();

        return array_map(static fn(array $data) => Authentication::fromArray($data), $data);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findById(string $uuid, bool $throwsOnError = false): Authentication
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('id', $uuid)
            ->limit(1)
            ->execute();

        if (empty($data) && $throwsOnError) {
            throw new NotFoundHttpException("Client with id $uuid not found");
        }

        return Authentication::fromArray(array_shift($data));
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function create(array $data): Authentication
    {
        $uuid = Uuid::uuid4()->toString();

        $data['id'] = $uuid;

        (new InsertSafeQuery())
            ->from(self::tableName())
            ->data($data)
            ->execute();

        return self::findById($uuid);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function update(string $uuid, array $data): Authentication
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

    public static function can(string $userId, #[ExpectedValues([Constants::AUTHENTICATION_ADMINISTRATOR, Constants::AUTHENTICATION_CLIENT])] string $itemName): bool
    {
        return (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('user_id', $userId)
            ->where('item_name', $itemName)
            ->exists();
    }

    public static function findByUser(string $uuid): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('user_id', $uuid)
            ->execute();

        return empty($data) ? [] : $data;

    }
}