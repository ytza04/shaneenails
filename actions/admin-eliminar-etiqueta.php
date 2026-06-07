<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);

if ($id === 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=etiquetas');
    exit;
}

$pdo = conectar();

// Primero elimino las relaciones en galeria_etiquetas
$stmt = $pdo->prepare('DELETE FROM galeria_etiquetas WHERE etiqueta_id = ?');
$stmt->execute([$id]);

// Luego elimino la etiqueta
$stmt = $pdo->prepare('DELETE FROM etiquetas WHERE id = ?');
$stmt->execute([$id]);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=etiquetas');
exit;