<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=galeria');
    exit;
}

$archivo    = $_FILES['imagen'];
$extension  = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidos = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $permitidos) || $archivo['size'] > 5 * 1024 * 1024) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=galeria');
    exit;
}

$nombre_archivo = uniqid('gal_') . '.' . $extension;
$destino        = UPLOAD_GALERIA . $nombre_archivo;

if (move_uploaded_file($archivo['tmp_name'], $destino)) {
    $pdo         = conectar();
    $servicio_id = !empty($_POST['servicio_id']) ? intval($_POST['servicio_id']) : null;
    $descripcion = trim(htmlspecialchars($_POST['descripcion'] ?? ''));

    $stmt = $pdo->prepare('
        INSERT INTO galeria (imagen_url, servicio_id, descripción)
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$nombre_archivo, $servicio_id, $descripcion]);
}


if (move_uploaded_file($archivo['tmp_name'], $destino)) {
    $pdo         = conectar();
    $servicio_id = !empty($_POST['servicio_id']) ? intval($_POST['servicio_id']) : null;
    $descripcion = trim(htmlspecialchars($_POST['descripcion'] ?? ''));

    $stmt = $pdo->prepare('
        INSERT INTO galeria (imagen_url, servicio_id, descripción)
        VALUES (?, ?, ?)
    ');
    $stmt->execute([$nombre_archivo, $servicio_id, $descripcion]);

    // Guardo el id de la foto recién insertada
    $foto_id = $pdo->lastInsertId();

    // Se insertan las etiquetas seleccionadas
    if (!empty($_POST['etiquetas'])) {
        $stmt = $pdo->prepare('
            INSERT INTO galeria_etiquetas (galeria_id, etiqueta_id) VALUES (?, ?)
        ');
        foreach ($_POST['etiquetas'] as $etiqueta_id) {
            $stmt->execute([$foto_id, intval($etiqueta_id)]);
        }
    }
}

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=galeria&ok=1');
exit;