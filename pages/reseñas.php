<?php
require_once '../config/config.php';
require_once '../config/db.php';

$titulo = 'Reseñas';
$pdo    = conectar();
$error  = '';
$exito  = '';

// Procesamos el formulario si fue enviado

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['usuario_id'])) {

    $puntuacion  = intval($_POST['puntuacion']);
    $comentario  = trim(htmlspecialchars($_POST['comentario']));

    if ($puntuacion < 1 || $puntuacion > 5) {
        $error = 'Por favor selecciona una puntuación.';

    } elseif (empty($comentario)) {
        $error = 'Por favor escribe un comentario.';

    } else {
        // Verificamos que tenga al menos una cita confirmada
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM citas 
            WHERE usuario_id = ? AND estado = "confirmada"
        ');
        $stmt->execute([$_SESSION['usuario_id']]);
        $tiene_cita = $stmt->fetchColumn() > 0;

        if (!$tiene_cita) {
            $error = 'Solo puedes dejar una reseña si has tenido una cita confirmada.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO reseñas (usuario_id, comentario, puntuación, aprobada)
                VALUES (?, ?, ?, 0)
            ');
            $stmt->execute([
                $_SESSION['usuario_id'],
                $comentario,
                $puntuacion
            ]);

            $exito = 'Tu reseña fue enviada y será publicada una vez que Shaneel la apruebe.';
        }
    }
}

// Traemos las reseñas aprobadas con el nombre de la usuaria

$stmt = $pdo->query('
    SELECT r.*, u.nombre
    FROM reseñas r
    JOIN usuarios u ON r.usuario_id = u.id
    WHERE r.aprobada = 1
    ORDER BY r.fecha DESC
');
$reseñas = $stmt->fetchAll();

// Calculamos el promedio de puntuación
$promedio = 0;
if (count($reseñas) > 0) {
    $suma     = array_sum(array_column($reseñas, 'puntuación'));
    $promedio = round($suma / count($reseñas), 1);
}

// Verificamos si la usuaria autenticada puede dejar reseña
$puede_reseñar = false;
if (isset($_SESSION['usuario_id'])) {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM citas 
        WHERE usuario_id = ? AND estado = "confirmada"
    ');
    $stmt->execute([$_SESSION['usuario_id']]);
    $puede_reseñar = $stmt->fetchColumn() > 0;
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<main>

    <section class="pagina-cabecera">
        <p class="seccion-pretitulo">Lo que dicen</p>
        <h1 class="seccion-titulo">Reseñas</h1>
        <div class="separador"></div>

        <?php if (count($reseñas) > 0): ?>
            <div class="reseñas-promedio">
                <span class="promedio-numero"><?= $promedio ?></span>
                <div class="promedio-estrellas">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="<?= $i <= $promedio ? 'estrella-llena' : 'estrella-vacia' ?>">
                            &#9733;
                        </span>
                    <?php endfor; ?>
                </div>
                <span class="promedio-total">
                    <?= count($reseñas) ?> reseña<?= count($reseñas) > 1 ? 's' : '' ?>
                </span>
            </div>
        <?php endif; ?>
    </section>

    <!-- Reseñas publicadas -->
    <section class="seccion">
        <?php if ($reseñas): ?>
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
                    <p class="reseña-comentario">
                        "<?= htmlspecialchars($reseña['comentario']) ?>"
                    </p>
                    <p class="reseña-autora">
                        — <?= htmlspecialchars($reseña['nombre']) ?>
                    </p>
                    <p class="reseña-fecha">
                        <?= date('d/m/Y', strtotime($reseña['fecha'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p class="texto-centrado texto-suave">
                Aún no hay reseñas publicadas.
            </p>
        <?php endif; ?>
    </section>

    <!-- Formulario para dejar una reseña -->
    <section class="seccion seccion-beige">
        <div class="reseña-form-contenedor">
            <h2 class="seccion-titulo">Deja tu reseña</h2>
            <div class="separador"></div>

            <?php if (!isset($_SESSION['usuario_id'])): ?>
                <p class="texto-centrado texto-suave">
                    <a href="../login.php" style="color: var(--dorado);">
                        Inicia sesión
                    </a>
                    para dejar una reseña.
                </p>

            <?php elseif (!$puede_reseñar): ?>
                <p class="texto-centrado texto-suave">
                    Solo puedes dejar una reseña después de tu primera cita confirmada.
                </p>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($exito): ?>
                    <div class="alert alert-exito"><?= $exito ?></div>
                <?php endif; ?>

                <?php if (!$exito): ?>
                <form method="POST" action="reseñas.php">

                    <div class="form-group">
                        <label>Puntuación *</label>
                        <div class="estrellas-selector" id="estrellas-selector">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="estrella-seleccionable" 
                                      data-valor="<?= $i ?>">
                                    &#9733;
                                </span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="puntuacion" 
                               id="puntuacion-input" value="0">
                    </div>

                    <div class="form-group">
                        <label for="comentario">Comentario *</label>
                        <textarea id="comentario" name="comentario" 
                                  rows="4"
                                  placeholder="Cuéntanos tu experiencia con Shaneel..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary">
                        Enviar reseña
                    </button>

                </form>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>

</main>

<?php require_once '../includes/footer.php'; ?>