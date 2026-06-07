<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$foto_id = intval($_GET['id']);

if ($foto_id === 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=galeria');
    exit;
}

$pdo  = conectar();

// Primero obtengo el nombre del archivo para borrarlo del disco
$stmt = $pdo->prepare('SELECT imagen_url FROM galeria WHERE id = ?');
$stmt->execute([$foto_id]);
$foto = $stmt->fetch();

if ($foto) {
    // Se Borra el archivo físico del servidor
    $ruta = UPLOAD_GALERIA . $foto['imagen_url'];
    if (file_exists($ruta)) {
        unlink($ruta);
    }

    // Y se Borra el registro de la base de datos
    $stmt = $pdo->prepare('DELETE FROM galeria WHERE id = ?');
    $stmt->execute([$foto_id]);
}

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=galeria');
exit;