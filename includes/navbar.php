<nav class="navbar">
    <a href="<?= SITE_URL ?>" class="navbar-brand">ShaneeNails</a>

    <ul class="navbar-links">
        <li><a href="<?= SITE_URL ?>/pages/servicios.php">Servicios</a></li>
        <li><a href="<?= SITE_URL ?>/pages/galeria.php">Galería</a></li>
        <li><a href="<?= SITE_URL ?>/pages/reseñas.php">Reseñas</a></li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
            <!-- Si hay sesión activa, muestra opciones de usuario -->
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li><a href="<?= SITE_URL ?>/admin/dashboard.php">Panel Admin</a></li>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/cliente/dashboard.php">Mi Portal</a></li>
            <?php endif; ?>
            <li><a href="<?= SITE_URL ?>/logout.php">Cerrar sesión</a></li>
        <?php else: ?>
            <!-- Si no hay sesión, muestra login y registro -->
            <li><a href="<?= SITE_URL ?>/login.php">Iniciar sesión</a></li>
            <li><a href="<?= SITE_URL ?>/registro.php" class="btn-nav">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>