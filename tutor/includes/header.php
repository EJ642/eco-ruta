<?php
    session_name('TUTOR_SESSION');
    session_start();
    if (!isset($ruta)) { $ruta = ""; }

    if (!function_exists('validarRutaSegura')) {
        function validarRutaSegura($ruta) {
            if (empty($ruta)) {
                return false;
            }

            $ruta = filter_var($ruta, FILTER_SANITIZE_URL);
            $ruta = strtok($ruta, '#');

            if (strpos($ruta, '..') !== false) {
                return false;
            }

            if (!preg_match('#^/[A-Za-z0-9_\-./?=&%]*$#', $ruta)) {
                return false;
            }

            return $ruta;
        }
    }

    if (!function_exists('guardarUltimaRuta')) {
        function guardarUltimaRuta() {
            $rutaActual = $_SERVER['REQUEST_URI'] ?? '';
            $rutaSegura = validarRutaSegura($rutaActual);
            if ($rutaSegura) {
                // Guardar en cookie separada según el rol
                $rol = strtolower($_SESSION['rol'] ?? '');
                if ($rol === 'Padre') {
                    setcookie('ultima_ruta_padre', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                } elseif ($rol === 'tutor') {
                    setcookie('ultima_ruta_tutor', $rutaSegura, time() + 30 * 24 * 60 * 60, '/', '', false, true);
                }
            }
        }
    }

    // Seguridad: verificar sesión y token
    if (empty($_SESSION['active']) || empty($_SESSION['token'])) {
        header('location: '.$ruta.'Tutor/index.php');
        exit;
    }

    // Guardar la última ruta visitada solo si el usuario ya está autenticado
    guardarUltimaRuta();

    // Verificar expiración de sesión (1 minuto máximo para pruebas)
    $max_session_time = 30 * 60; // 30 minutos en segundos
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $max_session_time) {
        session_destroy();
        header('location: '.$ruta.'Tutor/index.php?error=sesion_expirada');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Menu Tutor</title>


    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo $ruta; ?>css/stylesDocente.css">
    <link href="<?php echo $ruta; ?>bt/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $ruta; ?>bt-icons/bootstrap-icons.min.css" rel="stylesheet">

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
                    <span class="name"><?php echo $_SESSION['usuario']; ?></span>
                    <span class="rol"><?php echo $_SESSION['rol']; ?></span>
                </div>
                <div class="user-icon exit-btn" id="exit-btn">
                    <i class="bx bx-exit"></i>
                </div>
            </div>
        </div>
    </div>
        