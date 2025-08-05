<?php

namespace ShortenIt\controllers;

use PHPMailer\PHPMailer\Exception;
use Random\RandomException;
use ShortenIt\helpers\AuthToken;
use ShortenIt\helpers\Controller;
use ShortenIt\helpers\Renderer;
use ShortenIt\helpers\Utils;
use ShortenIt\models\User;
use ShortenIt\repository\UserRepository;
use SimpleApiRest\attributes\Permission;
use SimpleApiRest\attributes\Route;
use SimpleApiRest\core\EmailService;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\exceptions\ServerErrorHttpException;
use SimpleApiRest\exceptions\UnauthorizedHttpException;
use SimpleApiRest\query\SelectSafeQuery;

class AdminController extends Controller
{

    #[Permission(['ADMINISTRATOR'])]
    #[Route('admin/users', [Route::ROUTER_GET])]
    public function actionIndex(): array
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $order = isset($_GET['order']) ? (string) $_GET['order'] : 'name:asc';

        $data = (new SelectSafeQuery())
            ->from('user')
            ->data([
                'id', 'name', 'email', 'phone_number', 'status',
                // certifications
                'position', 'driver_license', 'npi', 'professional_license', 'professional_license2', 'ahca', 'fars', 'cfars', 'cpr', 'first_aid', 'hipaa', 'osha', 'hiv_aids', 'domestic_violence', 'medical_error', 'infection_control', 'patient_rights',
            ]);

        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);

            $data = $data->whereGroup(function ($q) use ($search) {
                $q('name', 'LIKE', "%$search%");
                $q('email', 'LIKE', "%$search%");
                $q('phone_number', 'LIKE', "%$search%");
                $q('status', 'LIKE', "%$search%");

//                if (intval($search) == $search) {
//                    $q('access_count', '=', $search);
//                }
            });
        }

        $total = $data->count();

        $users = $data->applyQueryParams([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
        ])->execute();

        return [
            'data' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ];
    }

    /**
     * @throws NotFoundHttpException
     * @throws RandomException
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    #[Permission(['ADMINISTRATOR'])]
    #[Route('admin/users', [Route::ROUTER_POST])]
    public function actionCreate(): array
    {
        $password = Utils::generatePassword('??????-##????-???###');

        $user = UserRepository::create([
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'phone_number' => $this->data['phone_number'],
            'position' => $this->data['position'],
            'password' => $password,
        ]);

//        $content = Renderer::render('new-account', [
//            'email' => $this->data['email'],
//            'password' => $password,
//        ]);

//        $emailService = new EmailService();
//        $emailService->sendEmail($this->data['email'], 'New account created', $content);

        unset(
            $user->password_hash,
            $user->password_reset_token,
            $user->verification_token,
        );

        return [
            'message' => 'user created!',
            'status' => 201,
            'data' => $user,
        ];
    }

    /**
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    #[Permission(['@'])]
    #[Route('admin/user/{id}', [Route::ROUTER_PUT])]
    public function actionUpdate(string $id): array
    {
        $inputValues = [
            ['field' => 'name', 'allowDuplicate' => true],
            ['field' => 'email', 'allowDuplicate' => false],
            ['field' => 'phone_number', 'allowDuplicate' => false],
            ['field' => 'position', 'allowDuplicate' => true],
        ];

        $user = $this->updateUser($inputValues, $id);

        unset(
            $user->password_hash,
            $user->password_reset_token,
            $user->verification_token,
        );

        return [
            'message' => 'User updated.',
            'data' => $user,
        ];
    }

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    #[Permission(['@'])]
    #[Route('admin/user/{id}/certifications', [Route::ROUTER_PUT])]
    public function actionUpdateCertifications(string $id): array
    {
        $inputValues = [
            ['field' => 'npi', 'allowDuplicate' => false],
            ['field' => 'driver_license', 'allowDuplicate' => true],
            ['field' => 'professional_license', 'allowDuplicate' => true],
            ['field' => 'professional_license2', 'allowDuplicate' => true],
            ['field' => 'ahca', 'allowDuplicate' => true],
            ['field' => 'fars', 'allowDuplicate' => false],
            ['field' => 'cfars', 'allowDuplicate' => false],
            ['field' => 'cpr', 'allowDuplicate' => true],
            ['field' => 'first_aid', 'allowDuplicate' => true],
            ['field' => 'hipaa', 'allowDuplicate' => true],
            ['field' => 'osha', 'allowDuplicate' => true],
            ['field' => 'hiv_aids', 'allowDuplicate' => true],
            ['field' => 'domestic_violence', 'allowDuplicate' => true],
            ['field' => 'medical_error', 'allowDuplicate' => true],
            ['field' => 'infection_control', 'allowDuplicate' => true],
            ['field' => 'patient_rights', 'allowDuplicate' => true],
        ];

        $user = $this->updateUser($inputValues, $id);

        unset(
            $user->password_hash,
            $user->password_reset_token,
            $user->verification_token,
        );

        return [
            'message' => 'User updated.',
            'data' => $user,
        ];
    }

    /**
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws NotFoundHttpException
     */
    #[Permission(['ADMINISTRATOR'])]
    #[Route('admin/user/{uuid}', [Route::ROUTER_DELETE])]
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

    /**
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function updateUser(array $inputValues, string $id): User
    {
        $data = [];

        foreach ($inputValues as $inputValue) {
            if (isset($this->data[$inputValue['field']])) {
                $data[$inputValue['field']] = $this->data[$inputValue['field']];

                if (!$inputValue['allowDuplicate']) {
                    $exists = UserRepository::findByColumn($inputValue['field'], $this->data[$inputValue['field']]);

                    if ($exists && $exists->id != $id) {
                        throw new BadRequestHttpException("Field {$inputValue['field']} already exists.");
                    }
                }
            }
        }

        return UserRepository::update($id, $data);
    }

}