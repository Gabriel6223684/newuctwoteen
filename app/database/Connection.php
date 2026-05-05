<?php

namespace app\aatabase;

<<<<<<< HEAD
namespace app\database;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DriverManager;

final class Connection
{
    private static ?DBALConnection $instance = null;
    #Retorna a conexão DBAL — cria uma única vez por processo.
    public static function get(): DBALConnection
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        self::$instance = DriverManager::getConnection([
            'driver'   => 'pdo_pgsql',
            'host'     => $_ENV['DB_HOST']     ?? 'localhost',
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
=======
use Doctrine\DBAL\Connection;

class Database
{
    private static ?Connection $conn = null;

    public static function connection(): Connection
    {
        if (!self::$conn) {
            self::$conn = require __DIR__ . '/config/connection.php';
        }

        return self::$conn;
    }

    public static function qb()
    {
        return self::connection()->createQueryBuilder();
    }
>>>>>>> ed49be637a2dbf3b135d06bae66d9d0c3b746ae7
}
