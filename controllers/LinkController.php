<?php

namespace ShortenIt\controllers;

use Faker\Factory;
use ShortenIt\helpers\Controller;
use ShortenIt\repository\LinkRepository;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\RateLimit;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\SelectSafeQuery;

class LinkController extends Controller
{

    #[Route('links', [Route::ROUTER_GET])]
    #[Permission(['@'])]
    #[RateLimit(30, 10)]
    public function actionIndex(): array
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $order = isset($_GET['order']) ? (string) $_GET['order'] : 'original_url:asc';

        $data = (new SelectSafeQuery())
            ->from('links')
            ->data();

        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);

            $data = $data->whereGroup(function ($q) use ($search) {
                $q('original_url', 'LIKE', "%$search%");
                $q('short_code', 'LIKE', "%$search%");

                if (intval($search) == $search) {
                    $q('access_count', '=', $search);
                }
            });
        }

        $total = $data->count();

        $links = $data->applyQueryParams([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
        ])->execute();

        return [
            'data' => $links,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
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
        $exists = LinkRepository::exists('original_url', $this->data['url']);

        if ($exists) {
            throw new BadRequestHttpException('This element already exists.');
        }

        $link = LinkRepository::create([
            'url' => $this->data['url'],
        ]);

        return [
            'message' => 'link created!',
            'status' => 201,
            'data' => $link,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Permission(['@'])]
    #[Route('links/{code}', [Route::ROUTER_GET])]
    public function actionView(string $code): array
    {
        $link = LinkRepository::findByCode($code);

        if (!$link) {
            throw new NotFoundHttpException("Link with id $code not found");
        }

        return [
            'data' => $link,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Permission(['*'])]
    #[Route('links/{code}/stats', [Route::ROUTER_GET])]
    public function actionStat(string $code): array
    {
        $link = LinkRepository::findByCode($code);

        if (!$link) {
            throw new NotFoundHttpException("Link with id $code not found");
        }

        $link = LinkRepository::update($link->id, ['access_count' => $link->access_count + 1]);

        return [
            'data' => $link,
        ];
    }

    #[Permission(['@'])]
    #[Route('links/{uuid}', [Route::ROUTER_DELETE])]
    public function actionDelete(string $uuid): array
    {
        LinkRepository::delete($uuid);

        return [
            'message' => 'Link deleted.',
            'status' => 204,
        ];
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Permission(['@'])]
    #[Route('links/faker/{qty}', [Route::ROUTER_POST])]
    public function actionFaker(int $qty): array
    {
        $faker = Factory::create();

        for ($i = 0; $i < $qty; $i++) {
            LinkRepository::create(['url' => $faker->url()]);
        }

        return [
            'message' => 'Links added.',
            'status' => 201,
        ];
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    #[Permission(['@'])]
    #[Route('links/{id}', [Route::ROUTER_PUT])]
    public function actionUpdate(string $id): array
    {
        $data = [];

        if (isset($this->data['original_url'])) {
            $data['original_url'] = $this->data['original_url'];
        }
        if (isset($this->data['short_code'])) {
            $data['short_code'] = $this->data['short_code'];

            $exists = LinkRepository::findByCode($this->data['short_code']);

            if ($exists && $exists->id != $id) {
                throw new BadRequestHttpException('This short code already exists.');
            }
        }

        $link = LinkRepository::update($id, $data);

        return [
            'message' => 'Link updated',
            'data' => $link,
        ];
    }

}