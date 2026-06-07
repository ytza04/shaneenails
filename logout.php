<?php
require_once 'config/config.php';

// Destruir completamente la sesión
$_SESSION = [];
session_destroy();

// Redirigir al inicio
header('Location: ' . SITE_URL);
exit;