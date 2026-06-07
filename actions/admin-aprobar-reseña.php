<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id = intval($_GET['id']);

if ($id === 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=reseñas');
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('UPDATE reseñas SET aprobada = 1 WHERE id = ?');
$stmt->execute([$id]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=reseñas');
exit;