<?php

namespace ShortenIt\controllers;

use DateTime;
use ShortenIt\helpers\Constants;
use ShortenIt\helpers\Controller;
use ShortenIt\helpers\Date;
use ShortenIt\models\Client;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\RateLimit;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\query\SelectSafeQuery;

class ClientController extends Controller
{

    #[Route('clients', [Route::ROUTER_GET])]
    #[Permission(['@'])]
    #[RateLimit(30, 10)]
    public function actionIndex(): array
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $order = isset($_GET['order']) ? (string) $_GET['order'] : 'first_name:asc';

        $data = (new SelectSafeQuery())
            ->from('client')
            ->data();

        $this->processSearch($data);

        $total = $data->count();

        $clients = $data->applyQueryParams([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
        ])->execute();

        $clients = array_map(function(array $data) {
            return $this->clientsFormatted($data);
        }, $clients);

        return [
            'data' => $clients,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    private function clientsFormatted(array $data): array
    {
        $client = Client::fromArray($data);

        $class = '';

        if (!empty($client->initial_examination_date)) {
            $days = Date::businessDays($client->initial_examination_date);

            if ($days > 45 && $client->case_status == Constants::STATUS_ACTIVE) {
                $class = 'table-warning';
            } else if ($client->case_status == Constants::STATUS_ACTIVE) {
                $class = 'table-success';
            } else if ($client->case_status == Constants::STATUS_NEW) {
                $class = 'table-info';
            } else if ($client->case_status == Constants::STATUS_FAILED) {
                $class = 'table-danger';
            } else if ($client->case_status == Constants::STATUS_CLOSE) {
                $class = 'table-secondary';
            }
        }

        return [
            'id' => $client->id,
            'first_name' => $client->first_name,
            'middle_name' => $client->middle_name,
            'last_name' => $client->last_name,
            'dob' => Date::format($client->dob, 'Y-m-d', 'm/d/Y'),
            'gender' => $client->gender,
            'identification_type' => $client->identification_type,
            'identification_presented' => $client->identification_presented,
            'incident_report_number' => $client->incident_report_number,
            'incident_date' => Date::format($client->incident_date, 'Y-m-d', 'm/d/Y'),
            'case_status' => $client->case_status,
            'class' => $class,
        ];
    }

    private function processSearch(SelectSafeQuery &$query): void
    {
        if (!isset($_GET['search'])) {
            return;
        }

        $search = trim($_GET['search']);

        $searchItems = explode(',', $search);

        $allowedColumns = [
            'first_name', 'middle_name', 'last_name', 'gender', 'dob',
            'identification_type', 'identification_presented', 'incident_report_number',
            'case_status', 'incident_date',
        ];

        foreach ($searchItems as $item) {
            if (str_contains($item, ':')) {
                [$column, $value] = explode(':', $item, 2);

                if (in_array($column, $allowedColumns)) {
                    if ($column == 'case_status') {
                        $query = $query->where($column, $value);
                        continue;
                    }

                    if (in_array($column, ['dob', 'incident_date']) && ($date = DateTime::createFromFormat('m/d/Y', $value))) {
                        $value = $date->format('Y-m-d');
                    }

                    $query = $query->whereAdvanced($column, 'LIKE', "%$value%");
                }
            }
        }
    }

}