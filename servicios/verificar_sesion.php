<?php
function verificar_rol(array $rolesPermitidos): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['usuario_id']) || empty($_SESSION['active']) || empty($_SESSION['token'])) {
        header('Location: /EcoRuta/index.php');
        exit;
    }

    if (!in_array($_SESSION['rol'] ?? '', $rolesPermitidos, true)) {
        header('Location: /EcoRuta/router.php');
        exit;
    }

    $max_session_time = 30 * 60;
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $max_session_time) {
        session_destroy();
        header('Location: /EcoRuta/index.php?error=sesion_expirada');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();
}