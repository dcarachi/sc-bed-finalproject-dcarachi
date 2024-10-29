<?php
namespace com\icemalta\kahuna\api\model;

use \PDO;

class DBConnect
{
    private static ?self $singleton = null;
    private PDO $dbh;

    private function __construct()
    {
        $env = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/.env');
        $this->dbh = new PDO(
            "mysql:host=mariadb;dbname=kahuna",
            $env['DB_USER'],
            $env['DB_PASS'],
            [PDO::ATTR_PERSISTENT => true]
        );
    }

    public static function getInstance(): self
    {
        self::$singleton ??= new DBConnect();
        return self::$singleton;
    }

    public function getConnection(): PDO
    {
        return $this->dbh;
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }
}