<?php

class Cdao
{
    private static $Opdo = null;

    public function __construct()
    {
        $this->initConnection();
    }

    private function initConnection(): void
    {
        if (self::$Opdo !== null) {
            return;
        }

        // $dsn = 'mysql:host=localhost;dbname=appilla;charset=utf8mb4';
        // $user = 'root';
        // $pass = '';

        // $dsn = 'mysql:host=135.125.103.97;dbname=bookbazar;charset=utf8mb4';
        // $user = 'apiuser';
        // $pass = 'Frifri26082005*';

        $dsn = 'mysql:host=172.20.121.1;dbname=bookbazar;charset=utf8mb4';
        $user = 'Api_User';
        $pass = 'Frifri26082005*';

        $options = [];

        $defaultOptions = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $options = $options + $defaultOptions;

        self::$Opdo = new \PDO($dsn, $user, $pass, $options);
    }

    public static function getPdo(): \PDO
    {
        if (self::$Opdo === null) {
            throw new \RuntimeException("PDO non initialisé.");
        }
        return self::$Opdo;
    }

    public function execute(string $sql, array $params = []): array
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function executeInsert(string $sql, array $params = []): int
    {
        $stmt = self::getPdo()->prepare($sql);
        $stmt->execute($params);

        return (int) self::getPdo()->lastInsertId();
    }

    public function getLastInsertId(): int {
        return (int) self::$Opdo->lastInsertId();
    }
    
}