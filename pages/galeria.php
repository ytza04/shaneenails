<?php
require_once '../config/config.php';
require_once '../config/db.php';

$titulo = 'Galería';
$pdo    = conectar();

// Recibimos los filtros de la URL de forma segura
// intval() garantiza que solo sea un número entero
// Si no viene nada en la URL, el valor es 0

$filtro_servicio = isset($_GET['servicio']) ? intval($_GET['servicio']) : 0;
$filtro_etiqueta = isset($_GET['etiqueta']) ? intval($_GET['etiqueta'])  : 0;

// Construyo la consulta según el filtro

// En vez de escribir varias consultas diferentes, construyo una sola consulta que cambia según el filtro. Esto se llama consulta dinámica.

$sql    = 'SELECT g.*, s.nombre AS servicio_nombre 
           FROM galeria g
           LEFT JOIN servicios s ON g.servicio_id = s.id';

$params = []; // Array de parámetros para el prepared statement

if ($filtro_servicio > 0) {
    $sql     .= ' WHERE g.servicio_id = ?';
    $params[] = $filtro_servicio;

} elseif ($filtro_etiqueta > 0) {
    // Para filtrar por etiqueta necesitamos unir con galeria_etiquetas
    $sql     .= ' JOIN galeria_etiquetas ge ON g.id = ge.galeria_id
                  WHERE ge.etiqueta_id = ?';
    $params[] = $filtro_etiqueta;
}

$sql .= ' ORDER BY g.fecha_subida DESC';

$stmt  = $pdo->prepare($sql);
$stmt->execute($params);
$fotos = $stmt->fetchAll();

// Traemos los servicios para los botones de filtro
$servicios = $pdo->query('SELECT * FROM servicios WHERE activo = 1')->fetchAll();

// Traemos las etiquetas para los botones de filtro
$etiquetas = $pdo->query('SELECT * FROM etiquetas ORDER BY nombre')->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>

    <!-- Cabecera -->
    <section class="pagina-cabecera">
        <p class="seccion-pretitulo">Mis trabajos</p>
        <h1 class="seccion-titulo">Galería</h1>
        <div class="separador"></div>
        <p class="seccion-subtitulo">
            Cada diseño cuenta una historia diferente.
        </p>
    </section>

    <section class="seccion">

        <!-- FILTROS -->
        <div class="filtros-contenedor">

            <!-- Botón para quitar filtros -->
            <a href="galeria.php"
               class="filtro-btn <?= ($filtro_servicio === 0 && $filtro_etiqueta === 0) ? 'filtro-activo' : '' ?>">
                Todos
            </a>

            <!-- Filtros por servicio -->
            <?php foreach ($servicios as $servicio): ?>
                <a href="galeria.php?servicio=<?= $servicio['id'] ?>"
                   class="filtro-btn <?= $filtro_servicio === $servicio['id'] ? 'filtro-activo' : '' ?>">
                    <?= htmlspecialchars($servicio['nombre']) ?>
                </a>
            <?php endforeach; ?>

            <!-- Filtros por etiqueta -->
            <?php foreach ($etiquetas as $etiqueta): ?>
                <a href="galeria.php?etiqueta=<?= $etiqueta['id'] ?>"
                   class="filtro-btn <?= $filtro_etiqueta === $etiqueta['id'] ? 'filtro-activo' : '' ?>">
                    <?= htmlspecialchars($etiqueta['nombre']) ?>
                </a>
            <?php endforeach; ?>

        </div>

        <!-- GRID DE FOTOS -->
        <?php if ($fotos): ?>

            <div class="galeria-pagina-grid">
                <?php foreach ($fotos as $foto): ?>

                <div class="galeria-item">
                    <img src="../uploads/galeria/<?= htmlspecialchars($foto['imagen_url']) ?>"
                         alt="<?= htmlspecialchars($foto['descripción'] ?? 'Trabajo de ShaneeNails') ?>">

                    <div class="galeria-overlay">
                        <?php if ($foto['descripción']): ?>
                            <p class="galeria-overlay-descripcion">
                                <?= htmlspecialchars($foto['descripción']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($foto['servicio_nombre']): ?>
                            <span class="galeria-overlay-tag">
                                <?= htmlspecialchars($foto['servicio_nombre']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p class="texto-centrado texto-suave" style="padding: 3rem 0;">
                No hay fotos en esta categoría todavía.
            </p>
        <?php endif; ?>

    </section>

</main>

<?php require_once '../includes/footer.php'; ?>