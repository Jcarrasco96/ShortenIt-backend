<?php

use ShortenIt\helpers\Date;
use ShortenIt\models\User;
use SimpleApiRest\core\BaseApplication;

/** @var User $user */

$documents = [
    'driver_license' => 'Driver License',
    'professional_license' => 'Professional License',
    'professional_license2' => '2nd Professional License',
    'ahca' => 'AHCA',
    'cpr' => 'CPR',
    'first_aid' => 'FIRST AID',
    'hipaa' => 'HIPAA',
    'osha' => 'OSHA',
    'hiv_aids' => 'HIV/AIDS',
    'domestic_violence' => 'Domestic Violence',
    'medical_error' => 'Medical Error',
    'infection_control' => 'Infection Control',
    'patient_rights' => 'Patient Rights',
]

?>

<div style="font-family: Helvetica, Arial, 'DejaVu Sans', monospace; line-height: 1.2; background-color: #f0f0f0; padding: 20px;">
    <div style="max-width: 700px; margin: 0 auto; background-color: #fff; padding: 20px;">

        <p style="text-align: justify;">
            Dear <?= $user->name ?> (<?= $user->email ?>),
            <br><br>
            We are notifying you that one or more of your documents registered with UNIVERSAL HEALTH CARE COMMUNITY SERVICES are approaching their expiration date.
            <br><br>
            To keep your information up-to-date and avoid inconveniences, we recommend reviewing and updating the documents listed below:
        </p>

        <table style="border-collapse: collapse; width: 100%;">
            <thead>
            <tr>
                <th style="border: 1px solid #000; padding: 5px; text-align: center; width: 30%;">DOCUMENT</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: center; width: 30%;">DUE DATE</th>
                <th style="border: 1px solid #000; padding: 5px; text-align: center; width: 40%;">INFO</th>
            </tr>
            </thead>
            <tbody>

            <?php
            foreach ($documents as $field => $document) {
                $class = Date::dateClass($user->$field);
                $expirationText = Date::expirationText($user->$field);
                $value = Date::format($user->$field, 'Y-m-d', 'm/d/Y');

                $styleClass = match ($class) {
                    'text-danger' => 'color: rgb(220, 53, 69); font-weight: bold;',
                    'text-warning' => 'color: rgb(255, 193, 7); font-weight: bold;',
                    default => '',
                };

                echo <<< ROW
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">$document</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center;">$value</td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; $styleClass">$expirationText</td>
                    </tr>
                ROW;
            }
            ?>

            </tbody>
        </table>

        <p style="text-align: justify;">
            If you have any questions or need assistance with the update, please feel free to contact us at <a href="mailto:<?= BaseApplication::$config['params']['supportEmail'] ?>"><?= BaseApplication::$config['params']['supportEmail'] ?></a>.
            <br><br>
            Thank you for your attention.
            <br><br>
            Sincerely,
            <br>
            Human Resources Department
            <br>
            UNIVERSAL HEALTH CARE COMMUNITY SERVICES
        </p>
    </div>
</div>
