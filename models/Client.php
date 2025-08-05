<?php

namespace ShortenIt\models;

use JetBrains\PhpStorm\ExpectedValues;
use ShortenIt\helpers\Constants;
use ShortenIt\repository\UserRepository;
use SimpleApiRest\exceptions\NotFoundHttpException;
use SimpleApiRest\rest\Model;

/**
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class Client extends Model
{

    public string $id;
    public string $first_name;
    public string $middle_name;
    public string $last_name;
    public string $dob;

    #[ExpectedValues([
        Constants::GENDER_FEMALE,
        Constants::GENDER_MALE,
    ])]
    public string $gender;
    public string $ssn;
    public string $identification_type;
    public string $identification_presented;
    public string $incident_report_number;
    public string $incident_date;
    public string $insurance_carrier;
    public string $insurance_policy_number;
    public string $insurance_claim_number;
    public string $initial_examination_date;
    public string $created_by;
    public string $updated_by;
    public string $created_at;
    public string $updated_at;

    #[ExpectedValues([
        Constants::STATUS_ACTIVE,
        Constants::STATUS_CLOSE,
        Constants::STATUS_FAILED,
        Constants::STATUS_NEW,
    ])]
    public string $case_status;

    public string $createdByUser;
    public string $updatedByUser;

    protected const PROPS = [
        'id',
        'first_name',
        'middle_name',
        'last_name',
        'dob',
        'gender',
        'ssn',
        'identification_type',
        'identification_presented',
        'incident_report_number',
        'incident_date',
        'insurance_carrier',
        'insurance_policy_number',
        'insurance_claim_number',
        'initial_examination_date',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'case_status',
    ];

    /**
     * @throws NotFoundHttpException
     */
    public function loadRelation(string $relation_name): array|Model
    {
        if ($relation_name == 'createdBy') {
            $data = UserRepository::findById($this->created_by);
            unset($data->password_hash);

            $this->attributes[$relation_name] = $data;
        }
        if ($relation_name == 'updatedBy') {
            $data = UserRepository::findById($this->updated_by);
            unset($data->password_hash);

            $this->attributes[$relation_name] = $data;
        }

        return parent::loadRelation($relation_name);
    }

    public function __get(string $name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            try {
                return $this->loadRelation($name);
            } catch (NotFoundHttpException) {

            }
        }

        return null;
    }

}