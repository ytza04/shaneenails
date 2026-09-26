<?php
function conectar() {
    $dbHost = env('DB_HOST');
    $dbName = env('DB_NAME');

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, env('DB_USER'), env('DB_PASS'), $opciones);
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}