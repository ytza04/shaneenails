<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Aquí cargo las variables de entorno desde el archivo .env, si existe.
// En producción (Railway) las variables ya están definidas en el entorno,
// por lo que no es necesario (ni seguro) tener un archivo .env en el repo.

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Ahora las variables están disponibles en $_ENV y getenv()

function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);

    return $value !== false && $value !== null ? $value : $default;
}

define('SITE_URL', env('SITE_URL'));
define('UPLOAD_REFERENCIAS', __DIR__ . '/../uploads/referencias/');
define('UPLOAD_GALERIA',     __DIR__ . '/../uploads/galeria/');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}