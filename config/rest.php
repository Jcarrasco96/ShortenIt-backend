<?php

return [
    'jwtSecretKey' => 'a6c97b34eb883228e640653165712092',
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
            'dbname' => 'oqcheck',
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
    'blockedIPsFile' => 'blocked_ips.txt',
    'mail' => [
        'host' => 'smtp.gmail.com',
        'username' => 'jcarrasco96joker@gmail.com',
        'password' => 'mhmdbacfnajoakts',
        'encryption' => 'tls',
        'port' => 587,
    ],
    'params' => [
        'supportEmail' => 'support@shortenit.com'
    ]
];