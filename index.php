<?php
require_once 'config/config.php';
require_once 'config/db.php';

$titulo = 'Inicio';

// Traigo los servicios activos para mostrarlos en la landing

$pdo      = conectar();
$stmt     = $pdo->query('SELECT * FROM servicios WHERE activo = 1 LIMIT 3');
$servicios = $stmt->fetchAll();

// Se muestran las últimas 6 fotos de la galería

$stmt   = $pdo->query('SELECT * FROM galeria ORDER BY fecha_subida DESC LIMIT 6');
$fotos  = $stmt->fetchAll();

// Se muestran reseñas aprobadas

$stmt    = $pdo->query('
    SELECT r.*, u.nombre 
    FROM reseñas r 
    JOIN usuarios u ON r.usuario_id = u.id 
    WHERE r.aprobada = 1 
    ORDER BY r.fecha DESC 
    LIMIT 3
');
$reseñas = $stmt->fetchAll();

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-contenido">
        <p class="hero-pretitulo">Bienvenida a</p>
        <h1 class="hero-titulo">ShaneeNails</h1>
        <p class="hero-descripcion">
            Uñas que cuentan tu historia. Semipermanente, gel y jelly tips 
            elaborados con dedicación y estilo en cada detalle.
        </p>
        <div class="hero-botones">
            <a href="pages/servicios.php" class="btn-primary">Ver servicios</a>
            <a href="pages/galeria.php" class="btn-secondary">Ver galería</a>
        </div>
    </div>
    <div class="hero-imagen">
        <div class="hero-imagen-marco">
            <img src="assets/img/hero.jpg" alt="Trabajos de ShaneeNails">
        </div>
    </div>
</section>

<!-- SERVICIOS -->
<section class="seccion seccion-beige">
    <p class="seccion-pretitulo">Lo que ofrecemos</p>
    <h2 class="seccion-titulo">Servicios</h2>
    <div class="separador"></div>
    <p class="seccion-subtitulo">Cada servicio es personalizado para ti</p>

    <div class="servicios-grid">
        <?php foreach ($servicios as $servicio): ?>
        <div class="servicio-card">
            <?php if ($servicio['imagen']): ?>
                <img src="uploads/galeria/<?= htmlspecialchars($servicio['imagen']) ?>" 
                     alt="<?= htmlspecialchars($servicio['nombre']) ?>"
                     class="servicio-imagen">
            <?php else: ?>
                <div class="servicio-imagen-placeholder"></div>
            <?php endif; ?>

            <div class="servicio-info">
                <h3><?= htmlspecialchars($servicio['nombre']) ?></h3>
                <p><?= htmlspecialchars($servicio['descripción']) ?></p>
                <p class="servicio-precio">
                    Desde <strong>$<?= number_format($servicio['precio_base'], 2) ?></strong>
                </p>
                <a href="pages/agendar.php?servicio=<?= $servicio['id'] ?>" 
                   class="btn-secondary">Solicitar cita</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- GALERIA PREVIEW -->

<section class="seccion">
    <p class="seccion-pretitulo">Nuestro trabajo</p>
    <h2 class="seccion-titulo">Galería</h2>
    <div class="separador"></div>

    <div class="galeria-grid">
        <?php if ($fotos): ?>
            <?php foreach ($fotos as $foto): ?>
            <div class="galeria-item">
                <img src="uploads/galeria/<?= htmlspecialchars($foto['imagen_url']) ?>"
                     alt="<?= htmlspecialchars($foto['descripción'] ?? 'Trabajo de ShaneeNails') ?>">
                <div class="galeria-overlay">
                    <p><?= htmlspecialchars($foto['descripción'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="texto-centrado">Próximamente galería de trabajos.</p>
        <?php endif; ?>
    </div>

    <div class="texto-centrado" style="margin-top: 2.5rem;">
        <a href="pages/galeria.php" class="btn-secondary">Ver galería completa</a>
    </div>
</section>

<!-- RESEÑAS -->
<?php if ($reseñas): ?>
<section class="seccion seccion-beige">
    <p class="seccion-pretitulo">Lo que dicen</p>
    <h2 class="seccion-titulo">Reseñas</h2>
    <div class="separador"></div>

    <div class="reseñas-grid">
        <?php foreach ($reseñas as $reseña): ?>
        <div class="reseña-card">
            <div class="reseña-estrellas">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="<?= $i <= $reseña['puntuación'] ? 'estrella-llena' : 'estrella-vacia' ?>">
                        &#9733;
                    </span>
                <?php endfor; ?>
            </div>
            <p class="reseña-comentario">"<?= htmlspecialchars($reseña['comentario']) ?>"</p>
            <p class="reseña-autora">— <?= htmlspecialchars($reseña['nombre']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- LLAMADA A LA ACCION -->
<section class="seccion cta-seccion">
    <h2>¿Lista para tu nueva manicura?</h2>
    <p>Agenda tu cita y cuéntanos el diseño que tienes en mente.</p>
    <a href="pages/agendar.php" class="btn-primary" 
       style="width: auto; margin-top: 1.5rem;">Agendar ahora</a>
</section>

<?php require_once 'includes/footer.php'; ?>