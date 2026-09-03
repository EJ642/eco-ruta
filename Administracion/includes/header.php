<?php
    session_name('ADMIN_SESSION');
    session_start();
    if (!isset($ruta)) { $ruta = ""; }

    $baseAdminUrl = '';
    if ($ruta !== '') {
        $baseAdminUrl = rtrim($ruta, '/') . '/';
    }
    $baseAdminUrl .= 'Administracion/';

    if (!function_exists('validarRutaSegura')) {
        function validarRutaSegura($ruta) {
            if (empty($ruta)) { return false; }
            $ruta = filter_var($ruta, FILTER_SANITIZE_URL);
            $ruta = strtok($ruta, '#');
            if (strpos($ruta, '..') !== false) { return false; }
            if (!preg_match('#^/[A-Za-z0-9_\-./?=&%]*$#', $ruta)) { return false; }
            return $ruta;
        }
    }

    if (!function_exists('guardarUltimaRuta')) {
        function guardarUltimaRuta() {
            $rutaActual = $_SERVER['REQUEST_URI'] ?? '';
            $rutaSegura = validarRutaSegura($rutaActual);
            if ($rutaSegura) {
                setcookie('ultima_ruta_admin', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
            }
        }
    }

    $pagina_actual = basename($_SERVER['PHP_SELF'] ?? '');
    $paginas_publicas = ['index.php', 'login.php', 'recuperar_clave.php'];

    if (!in_array($pagina_actual, $paginas_publicas, true)) {
        if (empty($_SESSION['active']) || empty($_SESSION['token'])) {
            header('location: ' . $baseAdminUrl . 'index.php');
            exit;
        }

        guardarUltimaRuta();

        $max_session_time = 30 * 60;
        if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $max_session_time) {
            session_destroy();
            header('location: ' . $baseAdminUrl . 'index.php?error=sesion_expirada');
            exit;
        }

        $_SESSION['ultimo_acceso'] = time();
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración - Santa Teresita</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/stylesDocente.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/alertify.min.css"/>
    <link rel="stylesheet" href="<?php echo $ruta; ?>alertify/themes/default.min.css"/>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <?php include __DIR__ . "/nav.php"; ?>
        <div class="footer">
            <ul class="menu">
                <li class="menu-item"><a href="#" class="menu-link"><i class='bx bx-cog'></i> Configuración</a></li>
            </ul>
            <div class="user">
                <div class="user-img">
                    <img src="<?php echo $ruta; ?>img/fondo-exmpl.jpg" alt="">
                </div>
                <div class="user-data">
                    <span class="name"><?php echo htmlspecialchars($_SESSION['usuario'] ?? 'Administrador'); ?></span>
                    <span class="rol"><?php echo htmlspecialchars($_SESSION['rol'] ?? 'Admin'); ?></span>
                </div>
                <div class="user-icon exit-btn" id="exit-btn">
                    <i class="bx bx-exit"></i>
                </div>
            </div>
        </div>
    </div>
