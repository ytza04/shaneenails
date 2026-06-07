<?php
require_once '../config/config.php';
require_once '../config/db.php';

// Este archivo responde en JSON, no en HTML
// Le decimos al navegador qué tipo de contenido vamos a enviar 

header('Content-Type: application/json');

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

// Se valida que sea una fecha real

if (empty($fecha) || !strtotime($fecha)) {
    echo json_encode([]);
    exit;
}

$pdo  = conectar();
$stmt = $pdo->prepare('
    SELECT t.id, t.hora, t.etiqueta
    FROM turnos t
    JOIN disponibilidad d ON t.id = d.turno_id
    WHERE d.fecha      = ?
    AND   d.disponible = 1
    AND   t.activo     = 1
    ORDER BY t.hora
');
$stmt->execute([$fecha]);
$turnos = $stmt->fetchAll();

// Formateamos la hora para mostrarla bonita
foreach ($turnos as &$turno) {
    $turno['hora_formato'] = date('h:i A', strtotime($turno['hora']));
}

// json_encode() convierte el array PHP en JSON que JavaScript puede leer directamente
echo json_encode($turnos);