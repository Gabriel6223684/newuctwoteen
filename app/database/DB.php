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

        self::$instance = new PDO(
            sprintf(
                'pgsql:host=%s;port=%d;dbname=%s',
                $host,
                $port,
                $_ENV['DB_NAME'] ?? ''
            ),
            $_ENV['DB_USER'] ?? '',
            $_ENV['DB_PASSWORD'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return self::$instance;
    }

    private function __construct() {}
}
