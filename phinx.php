<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

return [
    'paths' => [
        'migrations' => __DIR__ . '/App/Database/Migration',
        'seeds'      => __DIR__ . '/App/Database/Seed',
    ],

    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',

        'development' => [
            'adapter' => 'pgsql',
            'host' => 'postgres',
            'name' => 'development_db',
            'user' => 'senac',
            'pass' => 'senac',
            'port' => 5432,
        ],
    ],

    'version_order' => 'creation',
];
