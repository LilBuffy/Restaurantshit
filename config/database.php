<?php

class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbname = 'basta_masarap';
            $user = 'root';
            $pass = '';

            try {
                self::$connection = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('Sorry, the restaurant system is temporarily unavailable. Please try again later.');
            }
        }

        return self::$connection;
    }
}
