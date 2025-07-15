<?php

use SimpleApiRest\console\CLI;

// comment out the following line when deployed to production
defined('APP_ENV') or define('APP_ENV', 'dev');

require_once 'vendor/autoload.php';

$config = require_once 'config/rest.php';

(new CLI($config))->run($argv);
