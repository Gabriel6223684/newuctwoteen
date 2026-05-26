<?php

declare(strict_types=1);

namespace app\database;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DriverManager;

final class Connection
{
    private static ?DBALConnection $instance = null;
    #Retorna a conexão DBAL — cria uma única vez por processo.
    // Compatibilidade com testes: alguns testes chamam Connection::connection()
    public static function connection(): DBALConnection
    {
        return self::get();
    }

    public static function get(): DBALConnection
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => 'postgres',
            'port'     => (int) ($_ENV['DB_PORT'] ?? 5432),
            'dbname'   => $_ENV['DB_NAME']     ?? '',
            'user'     => $_ENV['DB_USER']     ?? '',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset'  => 'UTF8',
        ]);
        return self::$instance;
    }

    # Previne instanciação direta — uso exclusivo via Connection::get()
    private function __construct() {}
}
