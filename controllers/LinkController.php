<?php

namespace ShortenIt\controllers;

use ShortenIt\models\LinkModel;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\rest\Controller;

class LinkController extends Controller
{

    #[Route('links', [Route::ROUTER_GET])]
    #[Permission(['@'])]
    public function actionIndex(): array
    {
        $links = LinkModel::findAll();

//        sleep(4);

        return [
            'links' => $links,
        ];
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    #[Permission(['@'])]
    #[Route('links', [Route::ROUTER_POST])]
    public function actionCreate(): array
    {
        $exists = LinkModel::exists('original_url', $this->data['url']);

        if ($exists) {
            throw new BadRequestHttpException('This element already exists.');
        }

        $link = LinkModel::create([
            'url' => $this->data['url'],
        ]);

        if (empty($link)) {
            throw new BadRequestHttpException('Error creating link.');
        }

        return [
            'message' => 'link created!',
            'status' => 201,
            'link' => $link,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Permission(['@'])]
    #[Route('links/{code}', [Route::ROUTER_GET])]
    public function actionView(string $code): array
    {
        $link = LinkModel::findByCode($code);

        return [
            'link' => $link,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Permission(['*'])]
    #[Route('links/{code}/stats', [Route::ROUTER_GET])]
    public function actionStat(string $code): array
    {
        $link = LinkModel::findByCode($code);
        $link->access_count++;

        $link = LinkModel::update($link->id, ['access_count' => $link->access_count]);

        return [
            'link' => $link,
        ];
    }

    #[Permission(['@'])]
    #[Route('links/{uuid}', [Route::ROUTER_DELETE])]
    public function actionDelete(string $uuid): array
    {
        LinkModel::delete($uuid);

        return [
            'message' => 'Link deleted.',
            'status' => 204,
        ];
    }

}