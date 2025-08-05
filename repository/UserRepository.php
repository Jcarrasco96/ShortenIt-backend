<?php

namespace ShortenIt\repository;

use Ramsey\Uuid\Uuid;
use Random\RandomException;
use ShortenIt\helpers\Constants;
use ShortenIt\models\User;
use SimpleApiRest\core\Security;
use SimpleApiRest\exceptions\BadRequestHttpException;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\query\DeleteSafeQuery;
use SimpleApiRest\query\InsertSafeQuery;
use SimpleApiRest\query\SelectSafeQuery;
use SimpleApiRest\query\UpdateSafeQuery;
use SimpleApiRest\rest\Repository;

class UserRepository extends Repository
{

    protected static function tableName(): string
    {
        return 'user';
    }

    public static function findAll(): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->execute();

        return array_map(static fn(array $data) => User::fromArray($data), $data);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function findById(string $uuid, bool $throwsOnError = false): User
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('id', $uuid)
            ->limit(1)
            ->execute();

        if (empty($data) && $throwsOnError) {
            throw new NotFoundHttpException('User not found');
        }

        return User::fromArray(array_shift($data));
    }

    /**
     * @throws RandomException
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public static function create(array $data): User
    {
        $exist = self::existEmail($data['email']);

        if ($exist) {
            throw new BadRequestHttpException('Username or email already exists.');
        }

        $uuid = Uuid::uuid4()->toString();

        (new InsertSafeQuery())
            ->from(self::tableName())
            ->data([
                'id' => $uuid,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'position' => $data['position'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'password_hash' => Security::generatePasswordHash($data['password']),
                'status' => Constants::STATUS_ACTIVE,
            ])
            ->execute();

        return self::findById($uuid);
    }

    /**
     * @throws NotFoundHttpException
     */
    public static function update(string $uuid, array $data): User
    {
        unset($data['id']);

        (new UpdateSafeQuery())
            ->from(self::tableName())
            ->data($data)
            ->where('id', $uuid)
            ->execute();

        return self::findById($uuid);
    }

    public static function delete(string $uuid): bool
    {
        return (new DeleteSafeQuery())
            ->from(self::tableName())
            ->where('id', $uuid)
            ->execute();
    }

    /**
     * @throws BadRequestHttpException
     */
    public static function findByCredentials(string $email, string $password): User
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('email', $email)
            ->execute();

        $data = array_shift($data);

        if (empty($data)) {
            throw new BadRequestHttpException('Invalid email or password.');
        }

        if (!Security::validatePassword($password, $data['password_hash'])) {
            throw new BadRequestHttpException('Invalid email or password.');
        }

        return User::fromArray($data);
    }

    public static function exist(string $company): bool
    {
        return (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('company', $company)
            ->exists();
    }

    public static function existEmail(string $email): bool
    {
        return (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('email', $email)
            ->exists();
    }

    public static function findByEmail(string $email): ?User
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('email', $email)
            ->limit(1)
            ->execute();

        return empty($data) ? null : User::fromArray(array_shift($data));
    }

    public static function findByPhoneNumber(string $phone_number): ?User
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where('phone_number', $phone_number)
            ->limit(1)
            ->execute();

        return empty($data) ? null : User::fromArray(array_shift($data));
    }

    public static function findByColumn(string $column, mixed $value): ?User
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->where($column, $value)
            ->limit(1)
            ->execute();

        return empty($data) ? null : User::fromArray(array_shift($data));
    }

    public static function expiredRecords(string $date): array
    {
        $data = (new SelectSafeQuery())
            ->from(self::tableName())
            ->data()
            ->whereGroup(function ($q) use ($date) {
                $q('driver_license', '<=', $date);
                $q('professional_license', '<=', $date);
                $q('professional_license2', '<=', $date);
                $q('ahca', '<=', $date);
                $q('cpr', '<=', $date);
                $q('first_aid', '<=', $date);
                $q('hipaa', '<=', $date);
                $q('osha', '<=', $date);
                $q('hiv_aids', '<=', $date);
                $q('domestic_violence', '<=', $date);
                $q('medical_error', '<=', $date);
                $q('infection_control', '<=', $date);
                $q('patient_rights', '<=', $date);
            })
            ->orderBy('name')
            ->execute();

        return array_map(static function(array $data) {
            $user = User::fromArray($data);

            unset(
                $user->password_hash,
                $user->password_reset_token,
                $user->verification_token,
            );

            return $user;
        }, $data);
    }

}