<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$cita_id     = intval($_POST['cita_id']);
$precio      = floatval($_POST['precio_final']);
$mensaje     = trim(htmlspecialchars($_POST['mensaje'] ?? ''));

if ($cita_id === 0 || $precio <= 0) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=citas');
    exit;
}

$pdo = conectar();

// Insertamos la cotización
$stmt = $pdo->prepare('
    INSERT INTO cotizaciones (cita_id, precio_final, mensaje)
    VALUES (?, ?, ?)
');
$stmt->execute([$cita_id, $precio, $mensaje]);

// Actualizamos el estado de la cita a "cotizada"
$stmt = $pdo->prepare('
    UPDATE citas SET estado = "cotizada" WHERE id = ?
');
$stmt->execute([$cita_id]);

// Notificamos a la clienta
$stmt = $pdo->prepare('
    INSERT INTO notificaciones (usuario_id, mensaje)
    SELECT usuario_id, "Shaneel te ha enviado una cotización. Revísala en tu portal."
    FROM citas WHERE id = ?
');
$stmt->execute([$cita_id]);

// Después de: $stmt->execute([$cita_id]);
// Agrega esto:

require_once '../config/mail.php';

// Traemos los datos de la clienta y la cita para el email
$stmt = $pdo->prepare('
    SELECT u.email, u.nombre, s.nombre AS servicio,
           c.fecha, t.hora, c.modalidad
    FROM citas c
    JOIN usuarios u  ON c.usuario_id  = u.id
    JOIN servicios s ON c.servicio_id = s.id
    JOIN turnos t    ON c.turno_id    = t.id
    WHERE c.id = ?
');
$stmt->execute([$cita_id]);
$datos = $stmt->fetch();

emailCotizacionEnviada(
    $datos['email'],
    $datos['nombre'],
    number_format($precio, 2),
    $mensaje
);

header('Location: ' . SITE_URL . '/admin/dashboard.php?seccion=citas');
exit;


