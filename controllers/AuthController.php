<?php

namespace ShortenIt\controllers;

use ShortenIt\models\User;
use Random\RandomException;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\RateLimit;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\core\BaseApplication;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\rest\AuthorizationToken;
use SimpleApiRest\rest\Controller;

class AuthController extends Controller
{

    /**
     * @throws BadRequestHttpException
     */
    #[RateLimit]
    #[Route('auth/login', [Route::ROUTER_POST])]
    #[Permission(['?'])]
    public function actionLogin(): array
    {
        $user = User::findByCredentials($this->data['username'], $this->data['password']);

        if (empty($user)) {
            throw new BadRequestHttpException('Invalid username or password.');
        }

        $token = AuthorizationToken::createToken([
            'id' => $user->id,
            'username' => $user->username,
        ]);

        return [
            'message' => BaseApplication::t('Login successful!'),
            'token' => $token,
        ];
    }

    /**
     * @throws RandomException
     * @throws BadRequestHttpException
     */
    #[Permission(['?'])]
    #[Route('auth/register', [Route::ROUTER_POST])]
    public function actionRegister(): array
    {
        $user = User::create([
            'username' => $this->data['username'],
            'password' => $this->data['password'],
        ]);

        if (empty($user)) {
            throw new BadRequestHttpException('Invalid username or password.');
        }

        $folder = APP_ROOT . "/files/{$user->username}";

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $token = AuthorizationToken::createToken([
            'id' => $user->id,
            'username' => $user->username,
        ]);

        return [
            'message' => 'user registered!',
            'status' => 201,
            'token' => $token,
        ];
    }

}