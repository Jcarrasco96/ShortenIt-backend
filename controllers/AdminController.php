<?php

namespace ShortenIt\controllers;

use ShortenIt\helpers\AuthToken;
use ShortenIt\helpers\Controller;
use ShortenIt\repository\UserRepository;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;

class AdminController extends Controller
{

    #[Route('admin/users', [Route::ROUTER_GET])]
    public function actionIndex(): array
    {
        $data = UserRepository::findAll();

        //$dataNew = [];

//        foreach ($data as $user) {
//            /** @var User $user */
//
//            $dataNew[] = [
//                'id' => $user->id,
//                'username' => $user->username,
//                'last_logout_at' => $user->last_logout_at,
//                'permissions' => $user->permissions,
//            ];
//        }

        return [
            'message' => 'ok',
            'users' => $data,
            //'users2' => $dataNew,
        ];
    }

    /**
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    #[Permission(['ADMINISTRATOR'])]
    #[Route('admin/users/{uuid}', [Route::ROUTER_DELETE])]
    public function actionDelete(string $uuid): array
    {
        $token = AuthToken::dataToken();

        $userToken = UserRepository::findById($token['_id']);

        if ($userToken->id == $uuid) {
            throw new BadRequestHttpException('You cannot delete your own account.');
        }

        if (!UserRepository::delete($uuid)) {
            throw new BadRequestHttpException('Error deleting user.');
        }

        return [
            'message' => 'User deleted',
            'status' => 204,
        ];
    }

}