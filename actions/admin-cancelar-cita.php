<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$cita_id = intval($_GET['id']);

if ($cita_id === 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=citas');
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('UPDATE citas SET estado = "cancelada" WHERE id = ?');
$stmt->execute([$cita_id]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=citas');
exit;