<?php

namespace ShortenIt\tasks;

use ShortenIt\repository\UserSessionRepository;
use SimpleApiRest\cron\Task;

class DeleteExpiredSessions extends Task
{

    public function run(): void
    {
        UserSessionRepository::deleteExpiredSessions();
    }

}