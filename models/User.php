<?php

namespace ShortenIt\models;

use ShortenIt\repository\AuthenticationRepository;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\rest\Model;

/**
 *
 *  FOR USE RELATIONS
 *
 *  $users = array_map(function (User $data) {
 *      $data->loadRelation('permissions');
 *      return $data;
 *  }, UserRepository::findAll());
 *
 * @property Authentication[] $permissions
 */
class User extends Model
{

    public string $id;
    public string $company;
    public string $document_number;
    public string $email;
    public string $phone_number;
    public string $sms_number;
    public string $fax;
    public string $office_address;
    public string $password_hash;
    public ?string $password_reset_token;
    public ?string $verification_token;
    public string $status;
    public string $created_at;
    public string $updated_at;
    public string $created_by;
    public string $updated_by;
    public bool $notify_email;
    public bool $notify_app;
    public bool $notify_fax;
    public bool $notify_sms;
    public ?string $stripe_customer_id;
    public ?string $stripe_subscription_id;
    public string $subscription_type;
    public ?string $subscription_end;
    public string $ip_address;
    public ?string $code_sms;
    public bool $sms_validated;
    public ?string $last_logout_at;

    protected const PROPS = [
        'id',
        'company',
        'document_number',
        'email',
        'phone_number',
        'sms_number',
        'fax',
        'office_address',
        'password_hash',
        'password_reset_token',
        'verification_token',
        'status',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'notify_email',
        'notify_app',
        'notify_fax',
        'notify_sms',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_type',
        'subscription_end',
        'ip_address',
        'code_sms',
        'sms_validated',
        'last_logout_at',
    ];

    public array $permissions = [];

    /**
     * @throws NotFoundHttpException
     */
    public function loadRelation(string $relation_name): array {
        if ($relation_name === 'permissions') {
            $this->$relation_name = array_map(static function(array $data) {
                return Authentication::fromArray($data);
            } , AuthenticationRepository::findByUser($this->id));

            return $this->$relation_name;
        }

        throw new NotFoundHttpException("The relation '$relation_name' does not exist");
    }

}
