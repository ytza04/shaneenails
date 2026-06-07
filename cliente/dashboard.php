<?php
require_once '../config/config.php';
require_once '../config/db.php';

// Si no hay sesión activa, mandamos al login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Si es admin no debería estar aquí
if ($_SESSION['rol'] === 'admin') {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit;
}

$titulo = 'Mi Portal';
$pdo    = conectar();

// Leemos si viene de confirmar una cita exitosa
$cita_exitosa = isset($_GET['cita']) && $_GET['cita'] === 'exitosa';


// Traigo las citas que tiene la usuaria, junto con el nombre del servicio, la hora del turno y la cotización si existe
// Usamos JOIN para traer el nombre del servicio y la hora del turno en una sola consulta, en vez de hacer tres consultas separadas

$stmt = $pdo->prepare('
    SELECT 
        c.id,
        c.fecha,
        c.modalidad,
        c.estado,
        c.fecha_creacion,
        c.imagen_referencia,
        c.notas,
        s.nombre  AS servicio_nombre,
        s.precio_base,
        t.hora    AS turno_hora,
        t.etiqueta AS turno_etiqueta,
        co.precio_final,
        co.mensaje AS cotizacion_mensaje,
        co.estado  AS cotizacion_estado,
        co.id      AS cotizacion_id
    FROM citas c
    JOIN servicios s  ON c.servicio_id = s.id
    JOIN turnos t     ON c.turno_id    = t.id
    LEFT JOIN cotizaciones co ON c.id  = co.cita_id
    WHERE c.usuario_id = ?
    ORDER BY c.fecha DESC
');
$stmt->execute([$_SESSION['usuario_id']]);
$citas = $stmt->fetchAll();

// Separamos las citas activas de las canceladas
// array_filter() recorre el array y conserva solo los elementos que cumplan la condición que le pasamos

$citas_activas    = array_filter($citas, fn($c) => $c['estado'] !== 'cancelada');
$citas_canceladas = array_filter($citas, fn($c) => $c['estado'] === 'cancelada');

// Verificamos si hay cotizaciones pendientes de responder
$cotizaciones_pendientes = array_filter(
    $citas,
    fn($c) => $c['cotizacion_estado'] === 'pendiente'
);

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>

    <section class="pagina-cabecera">
        <p class="seccion-pretitulo">Bienvenida</p>
        <h1 class="seccion-titulo">
            <?= htmlspecialchars($_SESSION['nombre']) ?>
        </h1>
        <div class="separador"></div>
    </section>

    <section class="seccion">

        <!-- Mensaje de cita exitosa -->
        <?php if ($cita_exitosa): ?>
            <div class="alert alert-exito" style="max-width: 720px; margin: 0 auto 2rem;">
                Tu cita fue agendada correctamente. 
                Shaneel la revisará y te enviará una cotización pronto.
            </div>
        <?php endif; ?>

        <!-- Alerta de cotizaciones pendientes -->
        <?php if (count($cotizaciones_pendientes) > 0): ?>
            <div class="alert alert-cotizacion" style="max-width: 720px; margin: 0 auto 2rem;">
                Tienes <?= count($cotizaciones_pendientes) ?> 
                cotización(es) pendiente(s) de responder. 
                Revísalas más abajo.
            </div>
        <?php endif; ?>

        <!-- Botón agendar nueva cita -->
        <div class="texto-centrado" style="margin-bottom: 3rem;">
            <a href="../pages/agendar.php" class="btn-primary" 
               style="width: auto;">
                Agendar nueva cita
            </a>
        </div>

        <!-- Citas activas -->
             
        <div class="dashboard-seccion">
            <h2 class="dashboard-titulo">Mis citas</h2>

            <?php if (count($citas_activas) > 0): ?>
                <div class="citas-lista">
                    <?php foreach ($citas_activas as $cita): ?>
                    <div class="cita-card cita-estado-<?= $cita['estado'] ?>">

                        <!-- Cabecera de la tarjeta -->
                        <div class="cita-card-header">
                            <div>
                                <h3 class="cita-servicio">
                                    <?= htmlspecialchars($cita['servicio_nombre']) ?>
                                </h3>
                                <p class="cita-fecha-hora">
                                    <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                                    — 
                                    <?= date('h:i A', strtotime($cita['turno_hora'])) ?>
                                    (<?= htmlspecialchars($cita['turno_etiqueta']) ?>)
                                </p>
                            </div>
                            <!-- Badge de estado -->
                            <span class="cita-badge cita-badge-<?= $cita['estado'] ?>">
                                <?= ucfirst($cita['estado']) ?>
                            </span>
                        </div>

                        <!-- Detalles -->
                        <div class="cita-card-body">
                            <p class="cita-detalle">
                                <span class="cita-detalle-label">Modalidad</span>
                                <?= ucfirst($cita['modalidad']) ?>
                            </p>

                            <?php if ($cita['notas']): ?>
                            <p class="cita-detalle">
                                <span class="cita-detalle-label">Notas</span>
                                <?= htmlspecialchars($cita['notas']) ?>
                            </p>
                            <?php endif; ?>

                            <?php if ($cita['imagen_referencia']): ?>
                            <div class="cita-referencia">
                                <span class="cita-detalle-label">Tu referencia</span>
                                <img src="../uploads/referencias/<?= htmlspecialchars($cita['imagen_referencia']) ?>"
                                     alt="Imagen de referencia"
                                     class="cita-referencia-img">
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Cotización si existe -->
                        <?php if ($cita['cotizacion_id']): ?>
                        <div class="cotizacion-bloque cotizacion-<?= $cita['cotizacion_estado'] ?>">
                            <p class="cotizacion-titulo">Cotización de Shaneel</p>
                            <p class="cotizacion-precio">
                                $<?= number_format($cita['precio_final'], 2) ?>
                            </p>
                            <?php if ($cita['cotizacion_mensaje']): ?>
                                <p class="cotizacion-mensaje">
                                    "<?= htmlspecialchars($cita['cotizacion_mensaje']) ?>"
                                </p>
                            <?php endif; ?>

                            <!-- Botones solo si la cotización está pendiente -->
                            <?php if ($cita['cotizacion_estado'] === 'pendiente'): ?>
                                <div class="cotizacion-botones">
                                    <a href="../actions/cotizacion-responder.php?id=<?= $cita['cotizacion_id'] ?>&respuesta=aceptada"
                                       class="btn-primary" style="width: auto;">
                                        Aceptar
                                    </a>
                                    <a href="../actions/cotizacion-responder.php?id=<?= $cita['cotizacion_id'] ?>&respuesta=rechazada"
                                       class="btn-secondary">
                                        Rechazar
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="cotizacion-respondida">
                                    <?= $cita['cotizacion_estado'] === 'aceptada' 
                                        ? 'Aceptaste esta cotización' 
                                        : 'Rechazaste esta cotización' ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <p class="texto-centrado texto-suave" style="padding: 2rem 0;">
                    No tienes citas activas por el momento.
                </p>
            <?php endif; ?>
        </div>

        <!-- Historial de citas canceladas -->
            
        <?php if (count($citas_canceladas) > 0): ?>
        <div class="dashboard-seccion" style="margin-top: 4rem;">
            <h2 class="dashboard-titulo">Historial</h2>
            <div class="citas-lista">
                <?php foreach ($citas_canceladas as $cita): ?>
                <div class="cita-card cita-estado-cancelada">
                    <div class="cita-card-header">
                        <div>
                            <h3 class="cita-servicio">
                                <?= htmlspecialchars($cita['servicio_nombre']) ?>
                            </h3>
                            <p class="cita-fecha-hora">
                                <?= date('d/m/Y', strtotime($cita['fecha'])) ?>
                            </p>
                        </div>
                        <span class="cita-badge cita-badge-cancelada">
                            Cancelada
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </section>
</main>

<?php require_once '../includes/footer.php'; ?>