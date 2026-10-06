<?php
return [
    'driver' => 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'database' => getenv('DB_DATABASE') ?: 'gestion_academique',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
