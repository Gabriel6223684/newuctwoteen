<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

final class Connection
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        return self::get();
    }

    public static function get(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = (int) ($_ENV['DB_PORT'] ?? 5432);
        $db   = $_ENV['DB_NAME'] ?? 'development_db';
        $user = $_ENV['DB_USER'] ?? 'senac';
        $pass = $_ENV['DB_PASSWORD'] ?? 'senac';

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $host,
            $port,
            $db
        );

        self::$instance = new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return self::$instance;
    }

    private function __construct() {}
}
