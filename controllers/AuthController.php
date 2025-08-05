<?php

namespace ShortenIt\controllers;

use Random\RandomException;
use ShortenIt\helpers\AuthToken;
use ShortenIt\helpers\Constants;
use ShortenIt\helpers\Controller;
use ShortenIt\repository\AuthenticationRepository;
use ShortenIt\repository\UserRepository;
use ShortenIt\repository\UserSessionRepository;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\RateLimit;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\core\BaseApplication;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;

class AuthController extends Controller
{

    /**
     * @throws BadRequestHttpException
     * @throws RandomException
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    #[RateLimit]
    #[Route('auth/login', [Route::ROUTER_POST])]
    #[Permission(['?'])]
    public function actionLogin(): array
    {
        $user = UserRepository::findByCredentials($this->data['email'], $this->data['password']);

        if (!AuthenticationRepository::can($user->id, Constants::AUTHENTICATION_ADMINISTRATOR)) {
            throw new UnauthorizedHttpException('You cannot log in.');
        }

        [$access_token, $refresh_token] = UserSessionRepository::generateSessionToken($user);

        $user->loadRelation('permissions');

        return [
            'message' => BaseApplication::t('Login successful!'),
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'permissions' => array_column($user->permissions, 'item_name'),
        ];
    }

    /**
     * @throws RandomException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    #[RateLimit]
    #[Permission(['?'])]
    #[Route('auth/register', [Route::ROUTER_POST])]
    public function actionRegister(): array
    {
        $user = UserRepository::create([
            'username' => $this->data['username'],
            'email' => $this->data['email'],
            'password' => $this->data['password'],
        ]);

//        $content = Renderer::render('new-account', [
//            'email' => $this->data['email'],
//            'password' => $this->data['password'],
//        ]);

//        $emailService = new EmailService();
//        $emailService->sendEmail($user->email, 'New account created', '<h3>Thanks for signing up!</h3>');
//        $emailService->sendEmail('znnoreply@gmail.com', 'New account created', $content);

        [$access_token, $refresh_token] = UserSessionRepository::generateSessionToken($user);

        return [
            'status' => 201,
            'message' => 'user registered!',
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * @throws RandomException
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    #[RateLimit]
    #[Permission(['?'])]
    #[Route('auth/refresh-token', [Route::ROUTER_POST])]
    public function actionRefreshToken(): array
    {
        $refreshToken = $this->data['refresh_token'];

        [$access_token, $refresh_token] = UserSessionRepository::refreshSessionToken($refreshToken);

        return [
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
        ];
    }

    /**
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    #[Permission(['@'])]
    #[Route('auth/logout', [Route::ROUTER_POST])]
    public function actionLogout(): array
    {
        $dataToken = AuthToken::dataToken();

        $refreshToken = $this->data['refresh_token'] ?? null;

        if ($refreshToken) {
            UserSessionRepository::deleteUserRefreshToken($dataToken['_id'], $refreshToken);
        } else {
            UserSessionRepository::deleteAllUserRefreshToken($dataToken['_id']);
        }

        UserRepository::update($dataToken['_id'], [
            'last_logout_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'message' => 'UserComponent logged out!',
        ];
    }

}