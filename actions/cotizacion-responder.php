<?php
require_once '../config/config.php';
require_once '../config/db.php';

// Solo usuarias autenticadas
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$cotizacion_id = isset($_GET['id'])       ? intval($_GET['id'])       : 0;
$respuesta     = isset($_GET['respuesta']) ? $_GET['respuesta']        : '';

// Se valida que la respuesta sea un valor permitido
// Nunca confiamos en lo que viene de la URL directamente
$respuestas_validas = ['aceptada', 'rechazada'];

if ($cotizacion_id === 0 || !in_array($respuesta, $respuestas_validas)) {
    header('Location: ' . SITE_URL . '/cliente/dashboard.php');
    exit;
}

$pdo = conectar();

// Se verifica que esta cotización pertenece a esta usuaria
// Un usuario no puede responder cotizaciones de otras usuarias
$stmt = $pdo->prepare('
    SELECT co.id 
    FROM cotizaciones co
    JOIN citas c ON co.cita_id = c.id
    WHERE co.id = ? AND c.usuario_id = ?
');
$stmt->execute([$cotizacion_id, $_SESSION['usuario_id']]);

if (!$stmt->fetch()) {
    // La cotización no existe o no le pertenece
    header('Location: ' . SITE_URL . '/cliente/dashboard.php');
    exit;
}

// Aquí se actualiza el estado de la cotización
$stmt = $pdo->prepare('
    UPDATE cotizaciones SET estado = ? WHERE id = ?
');
$stmt->execute([$respuesta, $cotizacion_id]);

// Si aceptó, actualizo también el estado de la cita
if ($respuesta === 'aceptada') {
    $stmt = $pdo->prepare('
        UPDATE citas 
        SET estado = "confirmada" 
        WHERE id = (
            SELECT cita_id FROM cotizaciones WHERE id = ?
        )
    ');
    $stmt->execute([$cotizacion_id]);
}

// Después del UPDATE de la cita, si la respuesta fue "aceptada", se envía un correo de confirmación

if ($respuesta === 'aceptada') {
    require_once '../config/mail.php';

    $stmt = $pdo->prepare('
        SELECT u.email, u.nombre, s.nombre AS servicio,
               c.fecha, t.hora, c.modalidad
        FROM citas c
        JOIN usuarios u  ON c.usuario_id  = u.id
        JOIN servicios s ON c.servicio_id = s.id
        JOIN turnos t    ON c.turno_id    = t.id
        WHERE c.id = (SELECT cita_id FROM cotizaciones WHERE id = ?)
    ');
    $stmt->execute([$cotizacion_id]);
    $datos = $stmt->fetch();

    emailCitaConfirmada(
        $datos['email'],
        $datos['nombre'],
        [
            'servicio'  => $datos['servicio'],
            'fecha'     => date('d/m/Y', strtotime($datos['fecha'])),
            'hora'      => date('h:i A', strtotime($datos['hora'])),
            'modalidad' => ucfirst($datos['modalidad'])
        ]
    );
}

header('Location: ' . SITE_URL . '/cliente/dashboard.php');
exit;