<?php

namespace ShortenIt\controllers;

use ShortenIt\helpers\Constants;
use ShortenIt\helpers\Controller;
use ShortenIt\repository\UserRepository;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\exceptions\NotFoundHttpException;

class StaffInformationController extends Controller
{

    /**
     * @throws NotFoundHttpException
     */
    #[Permission([Constants::AUTHENTICATION_ADMINISTRATOR])]
    #[Route('staff/info/{uuid}')]
    public function actionInfo(string $uuid): array
    {
        $user = UserRepository::findById($uuid);

        unset($user->password_hash, $user->password_reset_token, $user->verification_token);

        return [
            'user' => $user,
        ];
    }

    #[Permission([Constants::AUTHENTICATION_ADMINISTRATOR])]
    #[Route('staff/renew')]
    public function actionRenew(): array
    {
        $monthAgo = date('Y-m-d', strtotime("-1 month"));

        $data = UserRepository::expiredRecords($monthAgo);

        return [
            'data' => $data,
            'month_ago' => $monthAgo,
        ];
    }

}