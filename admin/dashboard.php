<?php
require_once '../config/config.php';
require_once '../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$titulo  = 'Panel de administración';
$pdo     = conectar();
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'resumen';

$hoy = date('Y-m-d');

$stmt = $pdo->prepare('SELECT COUNT(*) FROM citas WHERE fecha = ? AND estado != "cancelada"');
$stmt->execute([$hoy]);
$citas_hoy = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM citas WHERE estado = "pendiente"');
$pendientes = $stmt->fetchColumn();

$stmt = $pdo->query('SELECT COUNT(*) FROM cotizaciones WHERE estado = "pendiente"');
$cotizaciones_pendientes = $stmt->fetchColumn();

$citas              = [];
$servicios          = [];
$disponibilidad     = [];
$reseñas_pendientes = [];
$etiquetas_admin    = [];

if ($seccion === 'citas') {
    $filtro_estado   = isset($_GET['estado']) ? $_GET['estado'] : '';
    $estados_validos = ['pendiente', 'confirmada', 'cotizada', 'cancelada'];
    $sql = '
        SELECT c.*, u.nombre AS cliente_nombre, u.email AS cliente_email,
               u.teléfono AS cliente_telefono, s.nombre AS servicio_nombre,
               t.hora AS turno_hora, t.etiqueta AS turno_etiqueta,
               co.id AS cotizacion_id, co.precio_final, co.estado AS cotizacion_estado
        FROM citas c
        JOIN usuarios u   ON c.usuario_id  = u.id
        JOIN servicios s  ON c.servicio_id = s.id
        JOIN turnos t     ON c.turno_id    = t.id
        LEFT JOIN cotizaciones co ON c.id  = co.cita_id
    ';
    $params = [];
    if (in_array($filtro_estado, $estados_validos)) {
        $sql     .= ' WHERE c.estado = ?';
        $params[] = $filtro_estado;
    }
    $sql .= ' ORDER BY c.fecha ASC, t.hora ASC';
    $stmt  = $pdo->prepare($sql);
    $stmt->execute($params);
    $citas = $stmt->fetchAll();
}

if ($seccion === 'servicios') {
    $servicios = $pdo->query('SELECT * FROM servicios ORDER BY nombre')->fetchAll();
}

if ($seccion === 'calendario') {
    $stmt = $pdo->query('
        SELECT d.fecha, d.turno_id, d.disponible, t.hora, t.etiqueta
        FROM disponibilidad d
        JOIN turnos t ON d.turno_id = t.id
        WHERE d.fecha >= CURDATE()
        ORDER BY d.fecha ASC, t.hora ASC
    ');
    $disponibilidad = $stmt->fetchAll();
}

if ($seccion === 'reseñas') {
    $reseñas_pendientes = $pdo->query('
        SELECT r.*, u.nombre FROM reseñas r
        JOIN usuarios u ON r.usuario_id = u.id
        ORDER BY r.aprobada ASC, r.fecha DESC
    ')->fetchAll();
}

if ($seccion === 'etiquetas') {
    $etiquetas_admin = $pdo->query('
        SELECT e.*, COUNT(ge.galeria_id) AS total_fotos
        FROM etiquetas e
        LEFT JOIN galeria_etiquetas ge ON e.id = ge.etiqueta_id
        GROUP BY e.id
        ORDER BY e.nombre
    ')->fetchAll();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="admin-main">

    <aside class="admin-sidebar">
        <p class="admin-sidebar-titulo">Panel</p>
        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-link <?= $seccion === 'resumen' ? 'admin-nav-activo' : '' ?>">Resumen</a>
            <a href="dashboard.php?seccion=citas" class="admin-nav-link <?= $seccion === 'citas' ? 'admin-nav-activo' : '' ?>">
                Citas
                <?php if ($pendientes > 0): ?><span class="admin-badge"><?= $pendientes ?></span><?php endif; ?>
            </a>
            <a href="dashboard.php?seccion=calendario" class="admin-nav-link <?= $seccion === 'calendario' ? 'admin-nav-activo' : '' ?>">Disponibilidad</a>
            <a href="dashboard.php?seccion=galeria" class="admin-nav-link <?= $seccion === 'galeria' ? 'admin-nav-activo' : '' ?>">Galería</a>
            <a href="dashboard.php?seccion=servicios" class="admin-nav-link <?= $seccion === 'servicios' ? 'admin-nav-activo' : '' ?>">Servicios</a>
            <a href="dashboard.php?seccion=etiquetas" class="admin-nav-link <?= $seccion === 'etiquetas' ? 'admin-nav-activo' : '' ?>">Etiquetas</a>
            <a href="dashboard.php?seccion=reseñas" class="admin-nav-link <?= $seccion === 'reseñas' ? 'admin-nav-activo' : '' ?>">Reseñas</a>
        </nav>
    </aside>

    <div class="admin-contenido">

        <?php if ($seccion === 'resumen'): ?>
            <h1 class="admin-titulo">Bienvenida, Shaneel</h1>
            <p class="admin-subtitulo"><?= date('l, d \d\e F \d\e Y') ?></p>
            <div class="admin-stats">
                <div class="stat-card">
                    <p class="stat-numero"><?= $citas_hoy ?></p>
                    <p class="stat-label">Citas hoy</p>
                </div>
                <div class="stat-card stat-alerta">
                    <p class="stat-numero"><?= $pendientes ?></p>
                    <p class="stat-label">Pendientes de revisar</p>
                </div>
                <div class="stat-card">
                    <p class="stat-numero"><?= $cotizaciones_pendientes ?></p>
                    <p class="stat-label">Cotizaciones sin respuesta</p>
                </div>
            </div>
            <a href="dashboard.php?seccion=citas&estado=pendiente" class="btn-primary" style="width: auto; margin-top: 1rem;">Ver citas pendientes</a>
        <?php endif; ?>

        <?php if ($seccion === 'citas'): ?>
            <h1 class="admin-titulo">Gestión de citas</h1>
            <div class="filtros-contenedor" style="justify-content: flex-start; margin-bottom: 2rem;">
                <a href="dashboard.php?seccion=citas" class="filtro-btn <?= empty($filtro_estado) ? 'filtro-activo' : '' ?>">Todas</a>
                <?php foreach (['pendiente', 'confirmada', 'cotizada', 'cancelada'] as $estado): ?>
                    <a href="dashboard.php?seccion=citas&estado=<?= $estado ?>" class="filtro-btn <?= $filtro_estado === $estado ? 'filtro-activo' : '' ?>"><?= ucfirst($estado) ?></a>
                <?php endforeach; ?>
            </div>
            <?php if ($citas): ?>
                <div class="admin-citas-lista">
                <?php foreach ($citas as $cita): ?>
                    <div class="admin-cita-card">
                        <div class="admin-cita-header">
                            <div>
                                <h3 class="admin-cita-cliente"><?= htmlspecialchars($cita['cliente_nombre']) ?></h3>
                                <p class="admin-cita-detalle"><?= htmlspecialchars($cita['servicio_nombre']) ?> &mdash; <?= date('d/m/Y', strtotime($cita['fecha'])) ?> <?= date('h:i A', strtotime($cita['turno_hora'])) ?></p>
                                <p class="admin-cita-detalle"><?= htmlspecialchars($cita['cliente_email']) ?> &mdash; <?= htmlspecialchars($cita['cliente_telefono']) ?></p>
                                <p class="admin-cita-detalle">Modalidad: <?= ucfirst($cita['modalidad']) ?></p>
                            </div>
                            <span class="cita-badge cita-badge-<?= $cita['estado'] ?>"><?= ucfirst($cita['estado']) ?></span>
                        </div>
                        <?php if ($cita['notas']): ?>
                            <p class="admin-cita-notas">"<?= htmlspecialchars($cita['notas']) ?>"</p>
                        <?php endif; ?>
                        <?php if ($cita['imagen_referencia']): ?>
                            <div style="padding: 0 1.5rem 1rem;">
                                <p class="cita-detalle-label">Referencia de la clienta</p>
                                <img src="../uploads/referencias/<?= htmlspecialchars($cita['imagen_referencia']) ?>" alt="Referencia" class="admin-referencia-img">
                            </div>
                        <?php endif; ?>
                        <div class="admin-cita-acciones">
                            <?php if ($cita['estado'] === 'pendiente' && !$cita['cotizacion_id']): ?>
                                <form method="POST" action="../actions/admin-cotizar.php">
                                    <input type="hidden" name="cita_id" value="<?= $cita['id'] ?>">
                                    <div class="admin-cotizar-form">
                                        <div class="form-group" style="margin: 0;">
                                            <input type="number" name="precio_final" placeholder="Precio en USD" step="0.01" min="0" required>
                                        </div>
                                        <div class="form-group" style="margin: 0;">
                                            <textarea name="mensaje" rows="2" placeholder="Mensaje para la clienta (opcional)"></textarea>
                                        </div>
                                        <button type="submit" class="btn-primary" style="width: auto;">Enviar cotización</button>
                                    </div>
                                </form>
                            <?php elseif ($cita['cotizacion_id']): ?>
                                <p class="admin-cotizacion-info">Cotización enviada: <strong>$<?= number_format($cita['precio_final'], 2) ?></strong> — Estado: <?= ucfirst($cita['cotizacion_estado']) ?></p>
                            <?php endif; ?>
                            <?php if ($cita['estado'] !== 'cancelada'): ?>
                                <a href="../actions/admin-cancelar-cita.php?id=<?= $cita['id'] ?>" class="btn-cancelar" onclick="return confirm('¿Segura que quieres cancelar esta cita?')">Cancelar cita</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="texto-suave">No hay citas con este filtro.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($seccion === 'calendario'): ?>
            <h1 class="admin-titulo">Gestionar disponibilidad</h1>
            <p class="admin-subtitulo">Activa o desactiva turnos para cada fecha.</p>
            <form method="POST" action="../actions/admin-disponibilidad.php">
                <div class="disponibilidad-grid">
                <?php
                $por_fecha = [];
                foreach ($disponibilidad as $d) { $por_fecha[$d['fecha']][] = $d; }
                ?>
                <?php foreach ($por_fecha as $fecha => $turnos): ?>
                    <div class="disponibilidad-dia">
                        <p class="disponibilidad-fecha"><?= date('d/m/Y', strtotime($fecha)) ?></p>
                        <?php foreach ($turnos as $turno): ?>
                            <label class="disponibilidad-turno">
                                <input type="checkbox" name="disponible[<?= $fecha ?>][<?= $turno['turno_id'] ?>]" value="1" <?= $turno['disponible'] ? 'checked' : '' ?>>
                                <?= $turno['etiqueta'] ?> — <?= date('h:i A', strtotime($turno['hora'])) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                </div>
                <div class="admin-nueva-fecha">
                    <h3 style="font-family: var(--fuente-titulo); color: var(--dorado); margin-bottom: 1rem;">Agregar nueva fecha</h3>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="nueva_fecha" min="<?= $hoy ?>">
                    </div>
                    <p class="form-ayuda">Se agregarán todos los turnos activos para esa fecha.</p>
                </div>
                <button type="submit" class="btn-primary" style="width: auto; margin-top: 1.5rem;">Guardar disponibilidad</button>
            </form>
        <?php endif; ?>

        <?php if ($seccion === 'galeria'): ?>
            <h1 class="admin-titulo">Gestionar galería</h1>
            <?php if (isset($_GET['ok'])): ?>
                <div class="alert alert-exito">Foto subida correctamente.</div>
            <?php endif; ?>
            <form method="POST" action="../actions/admin-subir-foto.php" enctype="multipart/form-data" class="admin-subir-form">
                <div class="form-group">
                    <label>Imagen *</label>
                    <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <div class="form-group">
                    <label>Servicio relacionado</label>
                    <select name="servicio_id">
                        <option value="">Sin categoría</option>
                        <?php
                        $svcs = $pdo->query('SELECT * FROM servicios WHERE activo = 1')->fetchAll();
                        foreach ($svcs as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" placeholder="Ej: Jelly tips con glitter dorado">
                </div>
                <div class="form-group">
                    <label>Etiquetas</label>
                    <?php $todas_etiquetas = $pdo->query('SELECT * FROM etiquetas ORDER BY nombre')->fetchAll(); ?>
                    <div class="etiquetas-checkboxes">
                        <?php foreach ($todas_etiquetas as $et): ?>
                            <label class="disponibilidad-turno">
                                <input type="checkbox" name="etiquetas[]" value="<?= $et['id'] ?>">
                                <?= htmlspecialchars($et['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="width: auto;">Subir foto</button>
            </form>
            <?php
            $fotos = $pdo->query('
                SELECT g.*, s.nombre AS servicio_nombre FROM galeria g
                LEFT JOIN servicios s ON g.servicio_id = s.id
                ORDER BY g.fecha_subida DESC
            ')->fetchAll();
            ?>
            <?php if ($fotos): ?>
                <div class="galeria-admin-grid" style="margin-top: 3rem;">
                    <?php foreach ($fotos as $foto): ?>
                    <div class="galeria-admin-item">
                        <img src="../uploads/galeria/<?= htmlspecialchars($foto['imagen_url']) ?>" alt="Foto galería">
                        <div class="galeria-admin-info">
                            <p><?= htmlspecialchars($foto['descripción'] ?? 'Sin descripción') ?></p>
                            <p class="texto-suave" style="font-size: 0.8rem;"><?= htmlspecialchars($foto['servicio_nombre'] ?? 'Sin categoría') ?></p>
                            <a href="../actions/admin-eliminar-foto.php?id=<?= $foto['id'] ?>" class="btn-cancelar" onclick="return confirm('¿Eliminar esta foto?')">Eliminar</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($seccion === 'servicios'): ?>
            <h1 class="admin-titulo">Gestionar servicios</h1>
            <?php if (isset($_GET['ok'])): ?>
                <div class="alert alert-exito">Cambios guardados correctamente.</div>
            <?php endif; ?>
            <?php if (isset($_GET['eliminado'])): ?>
                <div class="alert alert-exito">Servicio eliminado correctamente.</div>
            <?php endif; ?>
            <div class="admin-servicio-nuevo">
                <h2 class="dashboard-titulo" style="font-size: 1.4rem; margin-bottom: 1.5rem;">Agregar nuevo servicio</h2>
                <form method="POST" action="../actions/admin-agregar-servicio.php" class="admin-servicio-form">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required placeholder="Ej: Acrílico">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="2" placeholder="Describe brevemente el servicio"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Precio base (USD) *</label>
                        <input type="number" name="precio_base" step="0.01" min="0" required placeholder="0.00">
                    </div>
                    <button type="submit" class="btn-primary" style="width: auto;">Agregar servicio</button>
                </form>
            </div>
            <h2 class="dashboard-titulo" style="font-size: 1.4rem; margin: 3rem 0 1.5rem;">Servicios actuales</h2>
            <div class="admin-servicios-lista">
            <?php foreach ($servicios as $s): ?>
                <form method="POST" action="../actions/admin-editar-servicio.php" class="admin-servicio-form">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($s['nombre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="2"><?= htmlspecialchars($s['descripción']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Precio base (USD)</label>
                        <input type="number" name="precio_base" step="0.01" min="0" value="<?= $s['precio_base'] ?>" required>
                    </div>
                    <div class="admin-servicio-botones">
                        <button type="submit" class="btn-primary" style="width: auto;">Guardar cambios</button>
                        <a href="../actions/admin-eliminar-servicio.php?id=<?= $s['id'] ?>" class="btn-cancelar" onclick="return confirm('¿Segura que quieres eliminar este servicio?')">Eliminar servicio</a>
                    </div>
                </form>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($seccion === 'etiquetas'): ?>
            <h1 class="admin-titulo">Gestionar etiquetas</h1>
            <p class="admin-subtitulo">Las etiquetas ayudan a organizar las fotos de la galería.</p>
            <?php if (isset($_GET['ok'])): ?>
                <div class="alert alert-exito">Etiqueta agregada correctamente.</div>
            <?php endif; ?>
            <form method="POST" action="../actions/admin-agregar-etiqueta.php" class="admin-subir-form" style="max-width: 400px;">
                <div class="form-group">
                    <label>Nombre de la etiqueta *</label>
                    <input type="text" name="nombre" required placeholder="Ej: Temporada verano">
                </div>
                <button type="submit" class="btn-primary" style="width: auto;">Agregar etiqueta</button>
            </form>
            <div class="etiquetas-admin-lista" style="margin-top: 2rem;">
                <?php foreach ($etiquetas_admin as $e): ?>
                <div class="etiqueta-admin-item">
                    <div>
                        <span class="etiqueta-nombre"><?= htmlspecialchars($e['nombre']) ?></span>
                        <span class="etiqueta-total"><?= $e['total_fotos'] ?> foto<?= $e['total_fotos'] != 1 ? 's' : '' ?></span>
                    </div>
                    <a href="../actions/admin-eliminar-etiqueta.php?id=<?= $e['id'] ?>" class="btn-cancelar" onclick="return confirm('¿Eliminar esta etiqueta?')">Eliminar</a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($seccion === 'reseñas'): ?>
            <h1 class="admin-titulo">Gestionar reseñas</h1>
            <?php if ($reseñas_pendientes): ?>
                <div class="admin-reseñas-lista">
                <?php foreach ($reseñas_pendientes as $r): ?>
                    <div class="admin-reseña-card">
                        <div class="admin-reseña-header">
                            <div>
                                <p class="admin-cita-cliente"><?= htmlspecialchars($r['nombre']) ?></p>
                                <div class="reseña-estrellas" style="font-size: 1rem;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?= $i <= $r['puntuación'] ? 'estrella-llena' : 'estrella-vacia' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <span class="cita-badge <?= $r['aprobada'] ? 'cita-badge-confirmada' : 'cita-badge-pendiente' ?>"><?= $r['aprobada'] ? 'Publicada' : 'Pendiente' ?></span>
                        </div>
                        <p class="admin-cita-notas">"<?= htmlspecialchars($r['comentario']) ?>"</p>
                        <?php if (!$r['aprobada']): ?>
                            <div style="padding: 0 1.5rem 1.5rem;">
                                <a href="../actions/admin-aprobar-reseña.php?id=<?= $r['id'] ?>" class="btn-primary" style="width: auto;">Aprobar y publicar</a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="texto-suave">No hay reseñas por el momento.</p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>