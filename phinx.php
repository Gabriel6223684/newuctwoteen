<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => __DIR__ . '/app/database/Migration',
        'seeds' => __DIR__ . '/database/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'default',
        'default' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'name' => $_ENV['DB_NAME'] ?? 'development_db',
            'user' => $_ENV['DB_USER'] ?? 'senac',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'senac',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'charset' => 'utf8',
            'collation' => null,
        ],
    ],
    'version_order' => 'creation',
];
