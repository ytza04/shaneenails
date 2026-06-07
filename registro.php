<?php
require_once 'config/config.php';
require_once 'config/db.php';

$titulo = 'Registro';

// Si ya hay sesión activa no tiene sentido estar aquí

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';
$exito = '';

// Este bloque solo se ejecuta cuando el formulario es enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Recibir y limpiar datos
    // trim() elimina espacios al inicio y al final
    // htmlspecialchars() convierte caracteres peligrosos como < > en texto seguro
    $nombre   = trim(htmlspecialchars($_POST['nombre']));
    $email    = trim(htmlspecialchars($_POST['email']));
    $telefono = trim(htmlspecialchars($_POST['telefono']));
    $pass     = $_POST['contraseña'];         // La contraseña no se limpia, se hashea
    $pass2    = $_POST['contraseña_confirmar'];

    // 2. Validaciones básicas
    if (empty($nombre) || empty($email) || empty($pass)) {
        $error = 'Por favor completa todos los campos obligatorios.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';

    } elseif (strlen($pass) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';

    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';

    } else {
        // 3. Verificar que el email no esté registrado
        $pdo  = conectar();
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'Ya existe una cuenta con ese email.';
        } else {
            // 4. Hashear la contraseña y guardar
            $hash = password_hash($pass, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare('
                INSERT INTO usuarios (nombre, email, contraseña, teléfono, rol)
                VALUES (?, ?, ?, ?, "cliente")
            ');
            $stmt->execute([$nombre, $email, $hash, $telefono]);

            $exito = '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Crear cuenta</h1>
        <p class="auth-subtitle">Únete a ShaneeNails</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alert alert-exito"><?= $exito ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="nombre">Nombre completo *</label>
                <input type="text" id="nombre" name="nombre" required
                       value="<?= isset($nombre) ? $nombre : '' ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?= isset($email) ? $email : '' ?>">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono"
                       value="<?= isset($telefono) ? $telefono : '' ?>">
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña * (mínimo 8 caracteres)</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>

            <div class="form-group">
                <label for="contraseña_confirmar">Confirmar contraseña *</label>
                <input type="password" id="contraseña_confirmar" 
                       name="contraseña_confirmar" required>
            </div>

            <button type="submit" class="btn-primary">Crear cuenta</button>

        </form>

        <p class="auth-link">
            ¿Ya tienes cuenta? <a href="<?= SITE_URL ?>/login.php">Inicia sesión aquí</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>