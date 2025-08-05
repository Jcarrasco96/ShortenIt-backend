<?php

namespace ShortenIt\models;

use ShortenIt\repository\AuthenticationRepository;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\rest\Model;

/**
 *
 *  FOR USE RELATIONS
 *
 *  $users = array_map(function (UserComponent $data) {
 *      $data->loadRelation('permissions');
 *      return $data;
 *  }, UserRepository::findAll());
 *
 * @property Authentication[] $permissions
 */
class User extends Model
{

    public string $id;
    public string $name;
    public string $email;
    public string $phone_number;
    public string $password_hash;
    public ?string $password_reset_token;
    public ?string $verification_token;
    public string $status;
    public string $created_at;
    public string $updated_at;
    public ?string $created_by;
    public ?string $updated_by;
    public bool $notify_email;
    public bool $notify_app;
    public bool $notify_sms;
    public ?string $last_logout_at;

    public string $position;
    public ?string $driver_license;
    public ?string $npi;
    public ?string $professional_license;
    public ?string $professional_license2;
    public ?string $ahca;
    public ?string $fars;
    public ?string $cfars;
    public ?string $cpr;
    public ?string $first_aid;
    public ?string $hipaa;
    public ?string $osha;
    public ?string $hiv_aids;
    public ?string $domestic_violence;
    public ?string $medical_error;
    public ?string $infection_control;
    public ?string $patient_rights;

    protected const PROPS = [
        'id',
        'name',
        'email',
        'phone_number',
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
        'notify_sms',
        'last_logout_at',
        'position',
        'driver_license',
        'npi',
        'professional_license',
        'professional_license2',
        'ahca',
        'fars',
        'cfars',
        'cpr',
        'first_aid',
        'hipaa',
        'osha',
        'hiv_aids',
        'domestic_violence',
        'medical_error',
        'infection_control',
        'patient_rights',
    ];

    public array $permissions = [];

    /**
     * @throws NotFoundHttpException
     */
    public function loadRelation(string $relation_name): array {
        if ($relation_name === 'permissions') {
            $this->permissions = array_map(static function(array $data) {
                return Authentication::fromArray($data);
            } , AuthenticationRepository::findByUser($this->id));

            return $this->permissions;
        }

        return parent::loadRelation($relation_name);
    }

}
