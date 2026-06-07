<?php
require_once '../config/config.php';
require_once '../config/db.php';

$titulo = 'Servicios';
$pdo    = conectar();

// Traigo todos los servicios activos
// ORDER BY nombre los ordena alfabéticamente

$stmt     = $pdo->query('SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre');
$servicios = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>

    <!-- Cabecera de la página -->
    <section class="pagina-cabecera">
        <p class="seccion-pretitulo">Lo que ofrezco</p>
        <h1 class="seccion-titulo">Servicios</h1>
        <div class="separador"></div>
        <p class="seccion-subtitulo">
            Cada trabajo es único. Cuéntame tu idea y lo hacemos realidad.
        </p>
    </section>

    <!-- Listado de servicios -->
    <section class="seccion">
        <?php if ($servicios): ?>

            <div class="servicios-detalle-grid">
                <?php foreach ($servicios as $servicio): ?>

                <article class="servicio-detalle-card">

                    <!-- Imagen del servicio -->
                    <div class="servicio-detalle-imagen">
                        <?php if ($servicio['imagen']): ?>
                            <img src="../uploads/galeria/<?= htmlspecialchars($servicio['imagen']) ?>"
                                 alt="<?= htmlspecialchars($servicio['nombre']) ?>">
                        <?php else: ?>
                            <div class="servicio-sin-imagen"></div>
                        <?php endif; ?>
                    </div>

                    <!-- Información -->
                    <div class="servicio-detalle-info">

                        <h2><?= htmlspecialchars($servicio['nombre']) ?></h2>

                        <p class="servicio-detalle-descripcion">
                            <?= htmlspecialchars($servicio['descripción']) ?>
                        </p>

                        <!-- 
                            number_format() formatea un número con decimales.
                            number_format(20, 2) → "20.00"
                            Siempre úsalo para mostrar precios.
                        -->
                        <p class="servicio-detalle-precio">
                            Desde <span>$<?= number_format($servicio['precio_base'], 2) ?></span>
                        </p>

                        <p class="servicio-detalle-nota">
                            El precio final depende del diseño que elijas. 
                            Se te enviará una cotización personalizada 
                            después de ver tu referencia.
                        </p>

                        <!-- 
                            Pasamos el id del servicio en la URL con $_GET
                            para que el formulario sepa cuál fue elegido
                        -->
                        <a href="../pages/agendar.php?servicio=<?= $servicio['id'] ?>"
                           class="btn-primary" style="width: auto; display: inline-block;">
                            Solicitar cita
                        </a>

                    </div>
                </article>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- 
                Siempre se maneja el caso vacío.
                Una página en blanco sin explicación, puede confundir a la usuaria.
            -->
            <p class="texto-centrado texto-suave">
                Los servicios estarán disponibles próximamente.
            </p>
        <?php endif; ?>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>