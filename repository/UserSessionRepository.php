<?php

namespace ShortenIt\repository;

use Ramsey\Uuid\Uuid;
use Random\RandomException;
use ShortenIt\models\User;
use ShortenIt\models\UserSession;
use SimpleApiRest\core\SimpleJWT;
use SimpleApiRest\core\Utilities;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;
use SimpleApiRest\query\DeleteSafeQuery;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Repository;

class UserSessionRepository extends Repository
{

    protected static function tableName(): string
    {
        return 'user_sessions';
    }

    public static function findAll(): array
    {
        // TODO: Implement findAll() method.
        return [];
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findById(string $uuid, bool $throwsOnError = false): UserSession
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('id', $uuid)
            ->limit(1)
            ->execute();

        if (empty($data) && $throwsOnError) {
            throw new NotFoundHttpException("UserComponent session with id $uuid not found");
        }

        return UserSession::fromArray(array_shift($data));
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function create(array $data): UserSession
    {
        $uuid = Uuid::uuid4()->toString();

        $data['id'] = $uuid;
        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $data['ip_address'] = Utilities::getIp();

        if (is_numeric($data['expires_at'])) {
            $data['expires_at'] = date('Y-m-d H:i:s', $data['expires_at']);
        }

        (new InsertSafeQuery())
            ->from(self::tableName())
            ->data($data)
            ->execute();

        return self::findById($uuid);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function update(string $uuid, array $data): UserSession
    {
        unset($data['id']);

        if (is_numeric($data['expires_at'])) {
            $data['expires_at'] = date('Y-m-d H:i:s', $data['expires_at']);
        }

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

    /**
     * @throws NotFoundHttpException
     */
    public static function findByHash(string $hash): UserSession
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('refresh_hash', $hash)
            ->limit(1)
            ->execute();

        if (empty($data)) {
            throw new NotFoundHttpException("UserComponent session not found");
        }

        return UserSession::fromArray(array_shift($data));
    }

    public static function findActive(string $session_id): ?UserSession
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('id', $session_id)
            ->where('is_active', true)
            ->execute();

        return empty($data) ? null : UserSession::fromArray(array_shift($data));

    }

    public static function invalidateByUser(string $user_id): void
    {
        (new DeleteSafeQuery())
            ->from('refresh_tokens')
            ->where('user_id', $user_id)
            ->execute();
    }

    public static function deleteUserRefreshToken(string $user_id, string $tokenHash): void
    {
        (new DeleteSafeQuery())
            ->from(self::tableName())
            ->where('user_id', $user_id)
            ->where('refresh_hash', $tokenHash)
            ->execute();
    }

    public static function deleteAllUserRefreshToken(string $user_id): void
    {
        (new DeleteSafeQuery())
            ->from(self::tableName())
            ->where('user_id', $user_id)
            ->execute();
    }

    /**
     * @throws NotFoundHttpException
     * @throws RandomException
     */
    public static function generateSessionToken(User $user): array
    {
        $refresh_token = bin2hex(random_bytes(32));
        $expires_at = time() + 7 * 86400;

        $userSession = self::create([
            'user_id' => $user->id,
            'refresh_hash' => $refresh_token,
            'expires_at' => $expires_at,
        ]);

        $access_token = SimpleJWT::create([
            '_id' => $user->id,
            '_s' => $userSession->id,
            '_rt' => $refresh_token,
        ], self::TOKEN_EXPIRES);

        return [$access_token, $refresh_token];
    }

    /**
     * @throws RandomException
     * @throws UnauthorizedHttpException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public static function refreshSessionToken(?string $refreshToken): array
    {
        if (!isset($refreshToken)) {
            throw new BadRequestHttpException('Refresh token not set.');
        }

        $refresh_token = bin2hex(random_bytes(32));
        $expires_at = time() + 7 * 86400;

        $userSession = self::findByHash($refreshToken);

        if ($userSession->expires_at < time()) {
            self::invalidateByUser($userSession->user_id);
            throw new UnauthorizedHttpException('Refresh token invalid');
        }

        self::update($userSession->id, [
            'refresh_hash' => $refresh_token,
            'expires_at' => $expires_at,
        ]);

        $access_token = SimpleJWT::create([
            '_id' => $userSession->user_id,
            '_s' => $userSession->id,
            '_rt' => $refresh_token,
        ], self::TOKEN_EXPIRES);

        return [$access_token, $refresh_token];
    }

    private const TOKEN_EXPIRES = 2592000; // todo change in production (1 month)

    public static function deleteExpiredSessions(): void
    {
        (new DeleteSafeQuery())
            ->from(self::tableName())
//            ->data()
            ->whereAdvanced('expires_at', '<', date('Y-m-d H:i:s'))
            ->execute();
    }

}