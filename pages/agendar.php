<?php
require_once '../config/config.php';
require_once '../config/db.php';

// Solo usuarias registradas pueden agendar. Si no hay sesión activa, las mandamos al login

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL . '/login.php?redirect=agendar');
    exit;
}

$titulo = 'Agendar cita';
$pdo    = conectar();
$error  = '';

// Aquí detecto en qué paso estamos

$paso = isset($_POST['paso']) ? intval($_POST['paso']) : 1;

// Si viene un servicio preseleccionado desde la URL, lo guardamos en sesión para que no se pierda

if (isset($_GET['servicio']) && $paso === 1) {
    $_SESSION['agendar']['servicio_id'] = intval($_GET['servicio']);
}

// Se procesa sehún el paso que se envió

// --- PROCESAMOS PASO 1 ---

if ($paso === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre    = trim(htmlspecialchars($_POST['nombre']));
    $telefono  = trim(htmlspecialchars($_POST['telefono']));
    $modalidad = $_POST['modalidad'];
    $direccion = trim(htmlspecialchars($_POST['direccion'] ?? ''));
    $notas     = trim(htmlspecialchars($_POST['notas'] ?? ''));
    $servicio_id = intval($_POST['servicio_id']);

    // Validaciones
    if (empty($nombre) || empty($telefono) || empty($servicio_id)) {
        $error = 'Por favor completa todos los campos obligatorios.';
        $paso  = 1; // Nos quedamos en el paso 1

    } elseif ($modalidad === 'domicilio' && empty($direccion)) {
        $error = 'Por favor indica tu dirección para el servicio a domicilio.';
        $paso  = 1;

    } else {
        // Guardamos los datos del paso 1 en sesión
        $_SESSION['agendar'] = [
            'nombre'      => $nombre,
            'telefono'    => $telefono,
            'modalidad'   => $modalidad,
            'direccion'   => $direccion,
            'notas'       => $notas,
            'servicio_id' => $servicio_id,
        ];

        // Procesamos la imagen si la subieron
        if (isset($_FILES['imagen_referencia']) && $_FILES['imagen_referencia']['error'] === 0) {

            $archivo   = $_FILES['imagen_referencia'];
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

            // Solo permitimos imágenes
            $permitidos = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($extension, $permitidos)) {
                $error = 'Solo se permiten imágenes JPG, PNG o WEBP.';
                $paso  = 1;
            } elseif ($archivo['size'] > 5 * 1024 * 1024) {
                // 5MB máximo (5 * 1024 * 1024 bytes)
                $error = 'La imagen no puede pesar más de 5MB.';
                $paso  = 1;
            } else {
                // Generamos un nombre único para evitar conflictos
                // uniqid() genera un identificador único basado en el tiempo
                $nombre_archivo = uniqid('ref_') . '.' . $extension;
                $destino        = UPLOAD_REFERENCIAS . $nombre_archivo;

                if (move_uploaded_file($archivo['tmp_name'], $destino)) {
                    $_SESSION['agendar']['imagen_referencia'] = $nombre_archivo;
                } else {
                    $error = 'Hubo un problema al subir la imagen. Intenta de nuevo.';
                    $paso  = 1;
                }
            }
        }

        // Si no hubo errores, avanzamos al paso 2
        if (empty($error)) {
            $paso = 2;
        }
    }
}

// --- PROCESAMOS PASO 2 ---

if ($paso === 2 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {

    $fecha    = $_POST['fecha'];
    $turno_id = intval($_POST['turno_id']);

    if (empty($fecha) || $turno_id === 0) {
        $error = 'Por favor selecciona una fecha y un turno.';
        $paso  = 2;
    } else {
        $_SESSION['agendar']['fecha']    = $fecha;
        $_SESSION['agendar']['turno_id'] = $turno_id;
        $paso = 3;
    }
}

// --- PROCESAMOS PASO 3 (confirmación final) ---

if ($paso === 3 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {

    $datos = $_SESSION['agendar'];

    // Insertamos la cita en la base de datos
    $stmt = $pdo->prepare('
        INSERT INTO citas 
            (usuario_id, servicio_id, turno_id, fecha, modalidad, 
             dirección_domicilio, imagen_referencia, notas, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pendiente")
    ');

    $stmt->execute([
        $_SESSION['usuario_id'],
        $datos['servicio_id'],
        $datos['turno_id'],
        $datos['fecha'],
        $datos['modalidad'],
        $datos['direccion'] ?? null,
        $datos['imagen_referencia'] ?? null,
        $datos['notas'] ?? null,
    ]);

    // Guardamos el id de la cita recién creada
    $cita_id = $pdo->lastInsertId();

    // Creamos una notificación para Shaneel
    $stmt = $pdo->prepare('
        INSERT INTO notificaciones (usuario_id, mensaje)
        SELECT id, ? FROM usuarios WHERE rol = "admin"
    ');
    $stmt->execute(["Nueva cita agendada por {$datos['nombre']} para el {$datos['fecha']}"]);

    // Limpiamos los datos de agendamiento de la sesión
    // La sesión del usuario sigue activa, solo borramos los datos temporales
    unset($_SESSION['agendar']);


    require_once '../config/mail.php';

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

emailCitaAgendada(
    $datos['email'],
    $datos['nombre'],
    [
        'servicio'  => $datos['servicio'],
        'fecha'     => date('d/m/Y', strtotime($datos['fecha'])),
        'hora'      => date('h:i A', strtotime($datos['hora'])),
        'modalidad' => ucfirst($datos['modalidad'])
    ]
);

    // Redirigimos al portal de la clienta con un mensaje de éxito
    header('Location: ' . SITE_URL . '/cliente/dashboard.php?cita=exitosa');
    exit;
}

// Datos para mostrar en los formularios

$servicios = $pdo->query('SELECT * FROM servicios WHERE activo = 1')->fetchAll();

// Para el calendario del paso 2
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Traemos los días disponibles del mes seleccionado
$stmt = $pdo->prepare('
    SELECT DISTINCT fecha 
    FROM disponibilidad 
    WHERE disponible = 1 
    AND fecha >= CURDATE()
    AND MONTH(fecha) = ?
    AND YEAR(fecha) = ?
');
$stmt->execute([$mes, $anio]);

// Guardamos las fechas disponibles en un array simple para consultarlo fácil
$fechas_disponibles = array_column($stmt->fetchAll(), 'fecha');

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main class="agendar-main">

    <!-- Cabecera -->
    <section class="pagina-cabecera">
        <p class="seccion-pretitulo">Reserva tu lugar</p>
        <h1 class="seccion-titulo">Agendar cita</h1>
        <div class="separador"></div>
    </section>

    <section class="seccion">

        <!-- Indicador de pasos -->
        <div class="pasos-indicador">
            <div class="paso-item <?= $paso >= 1 ? 'paso-activo' : '' ?>">
                <div class="paso-numero">1</div>
                <span>Tus datos</span>
            </div>
            <div class="paso-linea"></div>
            <div class="paso-item <?= $paso >= 2 ? 'paso-activo' : '' ?>">
                <div class="paso-numero">2</div>
                <span>Fecha y hora</span>
            </div>
            <div class="paso-linea"></div>
            <div class="paso-item <?= $paso >= 3 ? 'paso-activo' : '' ?>">
                <div class="paso-numero">3</div>
                <span>Confirmar</span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- PASO 1: DATOS PERSONALES -->

        <?php if ($paso === 1): ?>

        <div class="agendar-card">
            <h2 class="agendar-subtitulo">Cuéntanos sobre ti</h2>

            <form method="POST" action="agendar.php" enctype="multipart/form-data">
                <!--
                    enctype="multipart/form-data" es OBLIGATORIO cuando el formulario
                    sube archivos. Sin esto, $_FILES estará vacío siempre.
                -->
                <input type="hidden" name="paso" value="1">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">Nombre completo *</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?= $_SESSION['agendar']['nombre'] ?? $_SESSION['nombre'] ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" required
                               value="<?= $_SESSION['agendar']['telefono'] ?? '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="servicio_id">Servicio *</label>
                    <select id="servicio_id" name="servicio_id" required>
                        <option value="">Selecciona un servicio</option>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= $s['id'] ?>"
                                <?= (isset($_SESSION['agendar']['servicio_id']) && 
                                    $_SESSION['agendar']['servicio_id'] == $s['id']) 
                                    ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['nombre']) ?> 
                                — Desde $<?= number_format($s['precio_base'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Modalidad *</label>
                    <div class="radio-group">
                        <label class="radio-opcion">
                            <input type="radio" name="modalidad" value="presencial"
                                   <?= (!isset($_SESSION['agendar']['modalidad']) || 
                                       $_SESSION['agendar']['modalidad'] === 'presencial') 
                                       ? 'checked' : '' ?>>
                            Presencial
                        </label>
                        <label class="radio-opcion">
                            <input type="radio" name="modalidad" value="domicilio"
                                   <?= (isset($_SESSION['agendar']['modalidad']) && 
                                       $_SESSION['agendar']['modalidad'] === 'domicilio') 
                                       ? 'checked' : '' ?>>
                            A domicilio
                        </label>
                    </div>
                </div>

                <!-- Este campo aparece solo si elige domicilio (lo manejamos con JS) -->
                <div class="form-group" id="campo-direccion" 
                     style="display: <?= (isset($_SESSION['agendar']['modalidad']) && 
                                         $_SESSION['agendar']['modalidad'] === 'domicilio') 
                                         ? 'flex' : 'none' ?>">
                    <label for="direccion">Dirección *</label>
                    <input type="text" id="direccion" name="direccion"
                           value="<?= $_SESSION['agendar']['direccion'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="imagen_referencia">
                        Imagen de referencia (opcional)
                    </label>
                    <input type="file" id="imagen_referencia" 
                           name="imagen_referencia" 
                           accept="image/jpeg,image/png,image/webp">
                    <small class="form-ayuda">
                        Sube una foto del diseño que tienes en mente. 
                        Máximo 5MB.
                    </small>
                </div>

                <div class="form-group">
                    <label for="notas">Notas adicionales</label>
                    <textarea id="notas" name="notas" rows="3" 
                              placeholder="Cuéntale a Shaneel cualquier detalle importante..."><?= 
                        $_SESSION['agendar']['notas'] ?? '' 
                    ?></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    Siguiente — Elegir fecha
                </button>
            </form>
        </div>

        <?php endif; ?>

        <!-- PASO 2: CALENDARIO -->

        <?php if ($paso === 2): ?>

        <div class="agendar-card">
            <h2 class="agendar-subtitulo">Elige tu fecha y horario</h2>

            <form method="POST" action="agendar.php">
                <input type="hidden" name="paso" value="2">

                <!-- Navegación del calendario -->
                <div class="calendario-nav">
                    <?php
                        // Calculamos el mes anterior y siguiente para la navegación
                        $mes_anterior  = $mes === 1  ? 12 : $mes - 1;
                        $anio_anterior = $mes === 1  ? $anio - 1 : $anio;
                        $mes_siguiente = $mes === 12 ? 1  : $mes + 1;
                        $anio_siguiente= $mes === 12 ? $anio + 1 : $anio;
                    ?>
                    <a href="agendar.php?mes=<?= $mes_anterior ?>&anio=<?= $anio_anterior ?>" 
                       class="cal-nav-btn">&larr;</a>

                    <h3 class="calendario-mes-titulo">
                        <?= strftime('%B %Y', mktime(0, 0, 0, $mes, 1, $anio)) ?>
                    </h3>

                    <a href="agendar.php?mes=<?= $mes_siguiente ?>&anio=<?= $anio_siguiente ?>" 
                       class="cal-nav-btn">&rarr;</a>
                </div>

                <!-- Grid del calendario -->
                <div class="calendario-grid">
                    <!-- Encabezados de días -->
                    <?php
                    $dias_semana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    foreach ($dias_semana as $dia): ?>
                        <div class="cal-header"><?= $dia ?></div>
                    <?php endforeach; ?>

                    <?php
                    // Primer día del mes y cuántos días tiene
                    $primer_dia   = date('w', mktime(0, 0, 0, $mes, 1, $anio));
                    $dias_en_mes  = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
                    $hoy          = date('Y-m-d');

                    // Celdas vacías antes del primer día
                    for ($i = 0; $i < $primer_dia; $i++): ?>
                        <div class="cal-dia cal-vacio"></div>
                    <?php endfor; ?>

                    <?php for ($dia = 1; $dia <= $dias_en_mes; $dia++):
                        $fecha_actual = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                        $disponible   = in_array($fecha_actual, $fechas_disponibles);
                        $pasado       = $fecha_actual < $hoy;
                        $es_hoy       = $fecha_actual === $hoy;
                    ?>
                        <div class="cal-dia 
                                    <?= $pasado      ? 'cal-pasado'     : '' ?>
                                    <?= $disponible  ? 'cal-disponible' : '' ?>
                                    <?= $es_hoy      ? 'cal-hoy'        : '' ?>"
                             <?= $disponible ? "data-fecha='$fecha_actual'" : '' ?>>
                            <?= $dia ?>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Campo oculto donde JS mete la fecha elegida -->
                <input type="hidden" name="fecha" id="fecha-elegida" value="">

                <!-- Sección de turnos (aparece al elegir fecha) -->
                <div id="turnos-seccion" style="display:none; margin-top: 2rem;">
                    <h3 class="agendar-subtitulo" style="font-size: 1.3rem;">
                        Horarios disponibles
                    </h3>
                    <div class="turnos-grid" id="turnos-lista"></div>
                    <input type="hidden" name="turno_id" id="turno-elegido" value="">
                </div>

                <button type="submit" class="btn-primary" 
                        id="btn-paso2" style="display:none; margin-top: 2rem;">
                    Siguiente — Confirmar cita
                </button>
            </form>
        </div>

        <?php endif; ?>

        <!-- PASO 3: RESUMEN Y CONFIRMACIÓN -->
        <?php if ($paso === 3): ?>

        <?php
            // Traemos el nombre del servicio y el turno para mostrarlos
            $stmt = $pdo->prepare('SELECT nombre FROM servicios WHERE id = ?');
            $stmt->execute([$_SESSION['agendar']['servicio_id']]);
            $servicio_nombre = $stmt->fetchColumn();

            $stmt = $pdo->prepare('SELECT hora FROM turnos WHERE id = ?');
            $stmt->execute([$_SESSION['agendar']['turno_id']]);
            $turno_hora = $stmt->fetchColumn();
        ?>

        <div class="agendar-card">
            <h2 class="agendar-subtitulo">Revisa tu cita antes de confirmar</h2>

            <div class="resumen-grid">
                <div class="resumen-item">
                    <span class="resumen-label">Nombre</span>
                    <span class="resumen-valor">
                        <?= htmlspecialchars($_SESSION['agendar']['nombre']) ?>
                    </span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Teléfono</span>
                    <span class="resumen-valor">
                        <?= htmlspecialchars($_SESSION['agendar']['telefono']) ?>
                    </span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Servicio</span>
                    <span class="resumen-valor">
                        <?= htmlspecialchars($servicio_nombre) ?>
                    </span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Fecha</span>
                    <span class="resumen-valor">
                        <?= date('d/m/Y', strtotime($_SESSION['agendar']['fecha'])) ?>
                    </span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Hora</span>
                    <span class="resumen-valor">
                        <?= date('h:i A', strtotime($turno_hora)) ?>
                    </span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-label">Modalidad</span>
                    <span class="resumen-valor">
                        <?= ucfirst($_SESSION['agendar']['modalidad']) ?>
                    </span>
                </div>
                <?php if (!empty($_SESSION['agendar']['direccion'])): ?>
                <div class="resumen-item">
                    <span class="resumen-label">Dirección</span>
                    <span class="resumen-valor">
                        <?= htmlspecialchars($_SESSION['agendar']['direccion']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($_SESSION['agendar']['notas'])): ?>
                <div class="resumen-item resumen-item-completo">
                    <span class="resumen-label">Notas</span>
                    <span class="resumen-valor">
                        <?= htmlspecialchars($_SESSION['agendar']['notas']) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <p class="resumen-nota">
                Shaneel revisará tu solicitud y te enviará una cotización 
                personalizada según el diseño que elegiste.
            </p>

            <form method="POST" action="agendar.php">
                <input type="hidden" name="paso" value="3">
                <input type="hidden" name="confirmar" value="1">

                <div class="resumen-botones">
                    <a href="agendar.php" class="btn-secondary">
                        Modificar
                    </a>
                    <button type="submit" class="btn-primary" style="width: auto;">
                        Confirmar cita
                    </button>
                </div>
            </form>
        </div>

        <?php endif; ?>

    </section>
</main>

<?php require_once '../includes/footer.php'; ?>