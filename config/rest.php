<?php

use ShortenIt\models\User;

return [
    'jwtSecretKey' => '',
    'origins' => [
        'http://localhost',
        'http://localhost:8080',
        'http://localhost:4200',
        'http://127.0.0.1:4200',
        'http://127.0.0.1:8022',
    ],
    'db' => [
        'driver' => 'mysql',
        'mysql' => [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'dbname' => 'test',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'sqlite' => [
            'path' => 'database.sqlite',
        ]
    ],
    'controllerNamespace' => 'ShortenIt\\controllers',
    'modelNamespace' => 'ShortenIt\\models',
    'repositoryNamespace' => 'ShortenIt\\repository',
    'userModel' => User::class,
    'blockedIPsFile' => 'blocked_ips.txt',
];