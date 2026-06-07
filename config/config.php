<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Aquí cargo las variables de entorno desde el archivo .env

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Ahora las variables están disponibles en $_ENV y getenv()

define('SITE_URL', $_ENV['SITE_URL']);
define('UPLOAD_REFERENCIAS', __DIR__ . '/../uploads/referencias/');
define('UPLOAD_GALERIA',     __DIR__ . '/../uploads/galeria/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}