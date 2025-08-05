<?php

use SimpleApiRest\console\CLI;
use SimpleApiRest\cron\Task;

// comment out the following line when deployed to production
defined('APP_ENV') or define('APP_ENV', 'dev');

require_once 'vendor/autoload.php';

$config = require_once 'config/rest.php';

$taskDir = __DIR__ . '/tasks';

foreach (glob("$taskDir/*.php") as $file) {
    require_once $file;
}

$taskClasses = array_filter(get_declared_classes(), function ($class) {
    return is_subclass_of($class, Task::class);
});

(new CLI($config))->execTasks($taskClasses);
