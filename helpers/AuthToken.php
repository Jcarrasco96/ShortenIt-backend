<?php

namespace ShortenIt\helpers;

use ShortenIt\repository\UserRepository;
use ShortenIt\repository\UserSessionRepository;
use SimpleApiRest\core\SimpleJWT;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;

class AuthToken
{

    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    public static function dataToken(): array
    {
        $token = self::token();

        $payload = SimpleJWT::verify($token);

        if (!$payload) {
            throw new UnauthorizedHttpException('Invalid token');
        }

        if (!self::isTokenStillValid($payload)) {
            throw new UnauthorizedHttpException('Expired token');
        }

        $session = UserSessionRepository::findActive($payload['_s']);

        if (!$session || $session->refresh_hash != $payload['_rt']) {
            throw new UnauthorizedHttpException('Invalid session or token revoked');
        }

        return $payload;
    }

    /**
     * @throws UnauthorizedHttpException
     */
    public static function token(): string
    {
        $headers = array_change_key_case(getallheaders());

        if (!isset($headers['authorization'])) {
            throw new UnauthorizedHttpException('Header Authorization not found.');
        }

        $parts = explode(' ', $headers['authorization']);

        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            throw new UnauthorizedHttpException('Invalid authorization header.');
        }

        return trim($parts[1]);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function isTokenStillValid($payload): bool
    {
        $userToken = UserRepository::findById($payload['_id']);

        return empty($userToken->last_logout_at) || strtotime($userToken->last_logout_at) < $payload['iat'];
    }

}