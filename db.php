<?php
declare(strict_types=1);

function db(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('LIFELINK_DB_HOST') ?: '127.0.0.1';
    $name = getenv('LIFELINK_DB_NAME') ?: 'lifelink';
    $user = getenv('LIFELINK_DB_USER') ?: 'root';
    $password = getenv('LIFELINK_DB_PASSWORD') ?: '';

    try {
        $connection = new PDO(
            "mysql:host={$host};dbname={$name};charset=utf8mb4",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        throw new RuntimeException('Database connection failed.', 0, $exception);
    }

    return $connection;
}
