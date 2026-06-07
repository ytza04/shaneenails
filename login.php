<?php
require_once 'config/config.php';
require_once 'config/db.php';

$titulo = 'Iniciar sesión';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . SITE_URL);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $pass  = $_POST['contraseña'];

    if (empty($email) || empty($pass)) {
        $error = 'Por favor completa todos los campos.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // password_verify() compara la contraseña escrita con el hash guardado
        if ($usuario && password_verify($pass, $usuario['contraseña'])) {

            // Guardar datos importantes en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['rol']        = $usuario['rol'];

            // Redirigir según el rol
            if ($usuario['rol'] === 'admin') {
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
            } else {
                header('Location: ' . SITE_URL . '/cliente/dashboard.php');
            }
            exit;

        } else {
            // Mensaje genérico por seguridad (no decimos si el email existe o no)
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<main class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Bienvenida</h1>
        <p class="auth-subtitle">Inicia sesión en ShaneeNails</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required
                       value="<?= isset($email) ? $email : '' ?>">
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" required>
            </div>

            <button type="submit" class="btn-primary">Iniciar sesión</button>

        </form>

        <p class="auth-link">
            ¿No tienes cuenta? <a href="<?= SITE_URL ?>/registro.php">Regístrate aquí</a>
        </p>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>