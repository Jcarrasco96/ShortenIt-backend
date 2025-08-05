<?php

namespace ShortenIt\helpers;

use ShortenIt\repository\AuthenticationRepository;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;
use SimpleApiRest\rest\Controller as RestController;

class Controller extends RestController
{

    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    protected function checkSpecialPermissions(array $permissions): bool
    {
        if (in_array('*', $permissions)) {
            return true;
        }

        $headers = array_change_key_case(getallheaders());

        if (in_array('?', $permissions) && !isset($headers['authorization'])) {
            return true;
        }

        if (in_array('@', $permissions)) {
            $token = AuthToken::dataToken();

            if (!empty($token)) {
                return true;
            }
        }

        $data = AuthToken::dataToken();

        $permissions = array_values(array_filter($permissions, fn($v) => !in_array($v, ['*', '@', '?'])));

        foreach ($permissions as $itemName) {
            if (AuthenticationRepository::can($data['_id'], $itemName)) {
                return true;
            }
        }

        return false;
    }
}