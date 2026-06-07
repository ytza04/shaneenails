<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$nombre      = trim(htmlspecialchars($_POST['nombre'] ?? ''));
$descripcion = trim(htmlspecialchars($_POST['descripcion'] ?? ''));
$precio      = floatval($_POST['precio_base'] ?? 0);

if (empty($nombre) || $precio <= 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios');
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('
    INSERT INTO servicios (nombre, descripción, precio_base, activo)
    VALUES (?, ?, ?, 1)
');
$stmt->execute([$nombre, $descripcion, $precio]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios&ok=1');
exit;