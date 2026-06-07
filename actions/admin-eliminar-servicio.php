<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios');
    exit;
}

$pdo = conectar();

// Verificamos si el servicio tiene citas asociadas
// Si las tiene no lo eliminamos, solo lo desactivamos
// Eliminar un servicio con citas rompería la integridad de los datos

$stmt = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE servicio_id = ?');
$stmt->execute([$id]);
$tiene_citas = $stmt->fetchColumn() > 0;

if ($tiene_citas) {
    // Solo lo desactivo para no perder el historial de citas
    $stmt = $pdo->prepare('UPDATE servicios SET activo = 0 WHERE id = ?');
    $stmt->execute([$id]);
} else {
    // Si no tiene citas, se elimina completamente
    $stmt = $pdo->prepare('DELETE FROM servicios WHERE id = ?');
    $stmt->execute([$id]);
}

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=servicios&eliminado=1');
exit;