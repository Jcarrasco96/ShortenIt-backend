<?php

namespace ShortenIt\tasks;

use PHPMailer\PHPMailer\Exception;
use ShortenIt\helpers\Renderer;
use ShortenIt\models\User;
use ShortenIt\repository\UserRepository;
use SimpleApiRest\core\EmailService;
use SimpleApiRest\cron\Task;
use SimpleApiRest\exceptions\ServerErrorHttpException;

class SendMailExpiredRecords extends Task
{

    public function run(): void
    {
        $monthAgo = date('Y-m-d', strtotime("-1 month"));

        $expiredRecords = UserRepository::expiredRecords($monthAgo);

        $emailService = new EmailService();

        foreach ($expiredRecords as $record) {
            /** @var User $record */

            try {
                $content = Renderer::render('due-document', [
                    'user' => $record,
                ]);

//                echo $content;
//                break;

                $emailService->sendEmail($record->email, 'DUE DOCUMENT', $content);
            } catch (ServerErrorHttpException|Exception $e) {
                $this->handleError($e);
            }

            break;
        }
    }
}