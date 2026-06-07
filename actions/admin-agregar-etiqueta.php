<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$nombre = trim(htmlspecialchars($_POST['nombre'] ?? ''));

if (empty($nombre)) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=etiquetas');
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('INSERT IGNORE INTO etiquetas (nombre) VALUES (?)');
$stmt->execute([$nombre]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=etiquetas&ok=1');
exit;