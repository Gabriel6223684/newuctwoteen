<?php

namespace app\database;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DriverManager;

final class Connection
{
    private static ?DBALConnection $instance = null;

    public static function get(): DBALConnection
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
'host'     => $_ENV['DB_HOST'] ?? 'postgres',
            'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
            'dbname'   => $_ENV['DB_NAME'] ?? 'development_db',
            'user'     => $_ENV['DB_USER'] ?? 'senac',
            'password' => $_ENV['DB_PASSWORD'] ?? 'senac',
            'charset'  => 'UTF8',
        ]);

        return self::$instance;
    }

    private function __construct() {}
}

class Database
{
    private static ?Connection $conn = null;

    public static function connection(): Connection
    {
        if (!self::$conn) {
            self::$conn = \app\database\Connection::get();
        }

        return self::$conn;
    }

    public static function qb()
    {
        return self::connection()->createQueryBuilder();
    }
}