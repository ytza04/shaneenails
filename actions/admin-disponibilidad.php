<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$pdo          = conectar();
$disponible   = $_POST['disponible'] ?? [];
$nueva_fecha  = $_POST['nueva_fecha'] ?? '';

// Aquí actualizo todos los turnos existentes a no disponible y luego activo solo los que sí vienen marcados

$stmt = $pdo->prepare('
    UPDATE disponibilidad SET disponible = 0 
    WHERE fecha >= CURDATE()
');
$stmt->execute();

// Activo los que vienen marcados en el formulario

foreach ($disponible as $fecha => $turnos) {
    foreach ($turnos as $turno_id => $valor) {
        $stmt = $pdo->prepare('
            UPDATE disponibilidad 
            SET disponible = 1
            WHERE fecha = ? AND turno_id = ?
        ');
        $stmt->execute([$fecha, intval($turno_id)]);
    }
}

// Si agregaron una nueva fecha, se inserta todos los turnos activos para ese día
if (!empty($nueva_fecha) && strtotime($nueva_fecha) >= strtotime(date('Y-m-d'))) {
    $turnos = $pdo->query('SELECT id FROM turnos WHERE activo = 1')->fetchAll();

    foreach ($turnos as $turno) {
        // INSERT IGNORE evita duplicados si la fecha y turno ya existen en la agenda
        
        $stmt = $pdo->prepare('
            INSERT IGNORE INTO disponibilidad (fecha, turno_id, disponible)
            VALUES (?, ?, 1)
        ');
        $stmt->execute([$nueva_fecha, $turno['id']]);
    }
}

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=calendario');
exit;