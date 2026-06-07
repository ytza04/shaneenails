<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id          = intval($_POST['id']);
$nombre      = trim(htmlspecialchars($_POST['nombre']));
$descripcion = trim(htmlspecialchars($_POST['descripcion'] ?? ''));
$precio      = floatval($_POST['precio_base']);

if ($id === 0 || empty($nombre) || $precio <= 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios');
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('
    UPDATE servicios 
    SET nombre = ?, descripción = ?, precio_base = ?
    WHERE id = ?
');
$stmt->execute([$nombre, $descripcion, $precio, $id]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios&ok=1');
exit;